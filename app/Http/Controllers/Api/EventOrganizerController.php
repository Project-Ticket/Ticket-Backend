<?php

namespace App\Http\Controllers\Api;

use App\Contracts\PaymentProviderInterface;
use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\EventOrganizer;
use App\Models\Setting;
use App\Services\PaymentService;
use App\Services\SettingService;
use App\Services\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventOrganizerController extends Controller
{
    protected $eventOrganizer, $paymentService, $paymentProvider;

    public function __construct(
        PaymentService $paymentService,
        PaymentProviderInterface $paymentProvider

    ) {
        $this->eventOrganizer = new EventOrganizer();
        $this->paymentService = $paymentService;
        $this->paymentProvider = $paymentProvider;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $this->eventOrganizer->query();

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('application_status')) {
                $query->where('application_status', $request->application_status);
            }

            if ($request->filled('verification_status')) {
                $query->where('verification_status', $request->verification_status);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $query->where('organization_name', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('city')) {
                $query->where('city', $request->city);
            }

            if ($request->filled('province')) {
                $query->where('province', $request->province);
            }

            $perPage = $request->get('per_page', 10);

            $eventOrganizers = $query->with([
                'user:id,name,email',
                'paymentMethod:id,name'
            ])
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $eventOrganizers->getCollection()->transform(function ($organizer) {
                $organizer->can_regenerate_invoice = in_array(
                    $organizer->payment_status,
                    ['pending', 'expired', 'failed', 'unpaid']
                );
                return $organizer;
            });

            return MessageResponseJson::paginated(
                'Event organizers retrieved successfully',
                $eventOrganizers
            );
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError(
                'Failed to fetch event organizers',
                ['error' => $th->getMessage()]
            );
        }
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        $profileIncomplete = $this->checkProfileCompleteness($user);

        if ($profileIncomplete) {
            return MessageResponseJson::badRequest(
                'Please complete your profile before registering as an Event Organizer',
                $profileIncomplete
            );
        }

        $existing = $this->eventOrganizer->where('user_id', Auth::id())->first();
        if ($existing) {
            return MessageResponseJson::conflict(
                'You have already registered as an Event Organizer'
            );
        }

        $validator = Validator::make($request->all(), [
            'organization_name'       => 'required|string|max:255',
            'description'             => 'nullable|string|max:5000',
            'logo'                    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner'                  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'website'                 => 'nullable|url|max:255',
            'instagram'               => 'nullable|url|max:255',
            'twitter'                 => 'nullable|url|max:255',
            'facebook'                => 'nullable|url|max:255',
            'address'                 => 'required|string|max:500',
            'city'                    => 'required|string|max:100',
            'province'                => 'required|string|max:100',
            'postal_code'             => 'required|string|max:10',
            'contact_person'          => 'required|string|max:100',
            'contact_phone'           => 'required|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'contact_email'           => 'required|email|max:255',
            'bank_name'               => 'nullable|string|max:100',
            'bank_account_number'     => 'nullable|string|max:50',
            'bank_account_name'       => 'nullable|string|max:100',
            'application_fee'         => 'nullable|numeric|min:0',
            'security_deposit'        => 'nullable|numeric|min:0',
            'required_documents'      => 'nullable|array',
            'required_documents.*'    => 'string|max:255',
            'uploaded_documents'      => 'nullable|array',
            'uploaded_documents.*'    => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(
                'Validation failed',
                $validator->errors()->toArray()
            );
        }

        DB::beginTransaction();

        try {
            $baseSlug = Str::slug($request->organization_name);
            $slug = $baseSlug;
            $counter = 1;

            while ($this->eventOrganizer->where('organization_slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('event-organizers/logos', 'public');
            }

            $bannerPath = null;
            if ($request->hasFile('banner')) {
                $bannerPath = $request->file('banner')->store('event-organizers/banners', 'public');
            }

            $uploadedDocuments = [];
            if ($request->hasFile('uploaded_documents')) {
                $user = Auth::user();

                $userIdentifier = Str::before($user->email, '@');

                foreach ($request->file('uploaded_documents') as $file) {
                    $originalName = $file->getClientOriginalName();

                    $fileName = pathinfo($originalName, PATHINFO_FILENAME)
                        . '_'
                        . $userIdentifier
                        . '_'
                        . now()->format('YmdHis')
                        . '.'
                        . $file->getClientOriginalExtension();

                    $path = $file->storeAs(
                        'event-organizers/documents',
                        $fileName,
                        'public'
                    );

                    $uploadedDocuments[] = $path;
                }
            }

            $applicationFee = SettingService::get('application_fee_event_organizer');

            $paymentMethod = $this->paymentService->getPaymentMethod($request->payment_method);
            if (!$paymentMethod) {
                return MessageResponseJson::badRequest('Payment method is not available');
            }

            $paymentFee = $this->paymentService->calculatePaymentFee($request->payment_method, $applicationFee);
            $totalAmount = $applicationFee + $paymentFee;

            $eventOrganizer = $this->eventOrganizer->create([
                'user_id'                 => Auth::id(),
                'organization_name'       => $request->organization_name,
                'organization_slug'       => $slug,
                'description'             => $request->description,
                'logo'                    => $logoPath,
                'banner'                  => $bannerPath,
                'website'                 => $request->website,
                'instagram'               => $request->instagram,
                'twitter'                 => $request->twitter,
                'facebook'                => $request->facebook,
                'address'                 => $request->address,
                'city'                    => $request->city,
                'province'                => $request->province,
                'postal_code'             => $request->postal_code,
                'contact_person'          => $request->contact_person,
                'contact_phone'           => $request->contact_phone,
                'contact_email'           => $request->contact_email,
                'bank_name'               => $request->bank_name,
                'bank_account_number'     => $request->bank_account_number,
                'bank_account_name'       => $request->bank_account_name,
                'application_fee'         => SettingService::get('application_fee_event_organizer'),
                'security_deposit'        => $request->security_deposit,
                'required_documents'      => $request->required_documents ? json_encode($request->required_documents) : null,
                'uploaded_documents'      => !empty($uploadedDocuments) ? json_encode($uploadedDocuments) : null,
                'application_submitted_at' => now(),
                'verification_status'     => 'pending',
                'application_status'      => 'pending',
                'status'                  => $this->eventOrganizer::STATUS_PENDING,
            ]);

            $invoiceData = [
                'external_id' => 'EO-Application-' . $eventOrganizer->uuid,
                'amount' => $totalAmount,
                'description' => "Event Organizer Application Fee - {$eventOrganizer->organization_name}",
                'currency' => 'IDR',
                'customer' => [
                    'given_names' => $eventOrganizer->organization_name,
                    'email' => $user->email,
                    "mobile_number" => $eventOrganizer->contact_phone,
                    "addresses" => [
                        [
                            "city" => $eventOrganizer->city,
                            "country" => "Indonesia",
                            "postal_code" => $eventOrganizer->postal_code,
                            "state" => $eventOrganizer->province,
                        ]
                    ]
                ],
                'payment_methods' => [$request->payment_method],
                "success_redirect_url" => $request->success_redirect_url,
                "failure_redirect_url" => $request->failure_redirect_url,
            ];

            $invoiceResult = $this->paymentProvider->createInvoice($invoiceData);

            $eventOrganizer->update([
                'payment_reference' => $invoiceResult['id'],
                'payment_method' => $request->payment_method
            ]);

            DB::commit();

            return MessageResponseJson::created('Event Organizer application submitted', [
                'event_organizer'   => $eventOrganizer->load('user:id,name,email'),
                'payment_url'       => $invoiceResult['invoice_url'],
                'invoice_id'        => $invoiceResult['id'],
                'total_amount'      => $totalAmount,
                'payment_method'    => $paymentMethod->name,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            if (isset($logoPath)) Storage::disk('public')->delete($logoPath);
            if (isset($bannerPath)) Storage::disk('public')->delete($bannerPath);
            if (!empty($uploadedDocuments)) {
                foreach ($uploadedDocuments as $doc) {
                    Storage::disk('public')->delete($doc);
                }
            }
            return MessageResponseJson::serverError(
                'Failed to create event organizer',
                ['error' => $th->getMessage()]
            );
        }
    }

    public function show(string $uuid): JsonResponse
    {
        try {
            $eventOrganizer = $this->eventOrganizer
                ->with(['user:id,name,email', 'reviewedBy:id,name'])
                ->where('uuid', $uuid)
                ->where('user_id', Auth::id())
                ->first();


            if (!$eventOrganizer) {
                return MessageResponseJson::notFound(
                    'Event organizer not found or unauthorized'
                );
            }

            return MessageResponseJson::success(
                'Event organizer data retrieved successfully',
                $eventOrganizer
            );
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError(
                'Failed to retrieve event organizer',
                ['error' => $th->getMessage()]
            );
        }
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'organization_name'       => 'nullable|string|max:255',
            'description'             => 'nullable|string|max:5000',
            'logo'                    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner'                  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'website'                 => 'nullable|url|max:255',
            'instagram'               => 'nullable|url|max:255',
            'twitter'                 => 'nullable|url|max:255',
            'facebook'                => 'nullable|url|max:255',
            'address'                 => 'nullable|string|max:500',
            'city'                    => 'nullable|string|max:100',
            'province'                => 'nullable|string|max:100',
            'postal_code'             => 'nullable|string|max:10',
            'contact_person'          => 'nullable|string|max:100',
            'contact_phone'           => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'contact_email'           => 'nullable|email|max:255',
            'bank_name'               => 'nullable|string|max:100',
            'bank_account_number'     => 'nullable|string|max:50',
            'bank_account_name'       => 'nullable|string|max:100',
            'application_fee'         => 'nullable|numeric|min:0',
            'security_deposit'        => 'nullable|numeric|min:0',
            'required_documents'      => 'nullable|array',
            'required_documents.*'    => 'string|max:255',
            'uploaded_documents'      => 'nullable|array',
            'uploaded_documents.*'    => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(
                'Validation failed',
                $validator->errors()->toArray()
            );
        }
        $eventOrganizer = $this->eventOrganizer->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->first();

        if (!$eventOrganizer) {
            return MessageResponseJson::notFound(
                'Event organizer not found or unauthorized'
            );
        }

        if (!$eventOrganizer->verification_status === 'verified') {
            return MessageResponseJson::forbidden(
                'Cannot update verified event organizer. Please contact admin for changes.'
            );
        }

        try {
            $updateData = [
                'organization_name' => $request->organization_name ?? $eventOrganizer->organization_name,
                'description'       => $request->description,
                'website'           => $request->website,
                'instagram'         => $request->instagram,
                'twitter'           => $request->twitter,
                'facebook'          => $request->facebook,
                'address'           => $request->address ?? $eventOrganizer->address,
                'city'              => $request->city ?? $eventOrganizer->city,
                'province'          => $request->province ?? $eventOrganizer->province,
                'postal_code'       => $request->postal_code ?? $eventOrganizer->postal_code,
                'contact_person'    => $request->contact_person ?? $eventOrganizer->contact_person,
                'contact_phone'     => $request->contact_phone ?? $eventOrganizer->contact_phone,
                'contact_email'     => $request->contact_email ?? $eventOrganizer->contact_email,
                'bank_name'         => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_account_name' => $request->bank_account_name,
                'application_fee'   => $request->application_fee,
                'security_deposit'  => $request->security_deposit,
                'required_documents' => $request->required_documents ? json_encode($request->required_documents) : null,
            ];

            if ($request->hasFile('logo')) {
                if ($eventOrganizer->logo) {
                    Storage::disk('public')->delete($eventOrganizer->logo);
                }
                $updateData['logo'] = $request->file('logo')->store('event-organizers/logos', 'public');
            }

            if ($request->hasFile('banner')) {
                if ($eventOrganizer->banner) {
                    Storage::disk('public')->delete($eventOrganizer->banner);
                }
                $updateData['banner'] = $request->file('banner')->store('event-organizers/banners', 'public');
            }

            $uploadedDocuments = json_decode($eventOrganizer->uploaded_documents, true) ?? [];

            if ($request->hasFile('uploaded_documents')) {
                foreach ($request->file('uploaded_documents') as $file) {
                    $uploadedDocuments[] = $file->store('event-organizers/documents', 'public');
                }
                $updateData['uploaded_documents'] = json_encode($uploadedDocuments);
            }

            if ($request->organization_name !== $eventOrganizer->organization_name) {
                $baseSlug = Str::slug($request->organization_name);
                $slug = $baseSlug;
                $counter = 1;

                while ($this->eventOrganizer->where('organization_slug', $slug)
                    ->where('id', '!=', $eventOrganizer->id)->exists()
                ) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                $updateData['organization_slug'] = $slug;
            }

            $eventOrganizer->update($updateData);

            DB::commit();

            return MessageResponseJson::success(
                'Event organizer updated successfully',
                $eventOrganizer->fresh()->load('user:id,name,email')
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError(
                'Failed to update event organizer',
                ['error' => $th->getMessage()]
            );
        }
    }

    public function destroy(string $uuid): JsonResponse
    {
        try {
            $eventOrganizer = $this->eventOrganizer->where('uuid', $uuid)
                ->where('user_id', Auth::id())
                ->first();

            if (!$eventOrganizer) {
                return MessageResponseJson::notFound(
                    'Event organizer not found or unauthorized'
                );
            }

            if ($eventOrganizer->verification_status === 'verified') {
                return MessageResponseJson::forbidden(
                    'Cannot delete verified event organizer. Please contact admin.'
                );
            }

            if ($eventOrganizer->logo) {
                Storage::disk('public')->delete($eventOrganizer->logo);
            }

            if ($eventOrganizer->banner) {
                Storage::disk('public')->delete($eventOrganizer->banner);
            }

            $uploadedDocuments = json_decode($eventOrganizer->uploaded_documents, true) ?? [];
            foreach ($uploadedDocuments as $document) {
                Storage::disk('public')->delete($document);
            }

            $eventOrganizer->delete();

            return MessageResponseJson::success('Event organizer deleted successfully');
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError(
                'Failed to delete event organizer',
                ['error' => $th->getMessage()]
            );
        }
    }

    public function resubmitApplication(Request $request, string $uuid): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'organization_name'         => 'required|string|max:255',
            'description'               => 'nullable|string|max:5000',
            'logo'                      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner'                    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'website'                   => 'nullable|url|max:255',
            'instagram'                 => 'nullable|url|max:255',
            'twitter'                   => 'nullable|url|max:255',
            'facebook'                  => 'nullable|url|max:255',
            'address'                   => 'required|string|max:500',
            'city'                      => 'required|string|max:100',
            'province'                  => 'required|string|max:100',
            'postal_code'               => 'required|string|max:10',
            'contact_person'            => 'required|string|max:100',
            'contact_phone'             => 'required|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'contact_email'             => 'required|email|max:255',
            'bank_name'                 => 'nullable|string|max:100',
            'bank_account_number'       => 'nullable|string|max:50',
            'bank_account_name'         => 'nullable|string|max:100',
            'security_deposit'          => 'nullable|numeric|min:0',
            'required_documents'        => 'nullable|array',
            'required_documents.*'      => 'string|max:255',
            'uploaded_documents'        => 'nullable|array',
            'uploaded_documents.*'      => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'rejection_reason_response' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(
                'Validation failed',
                $validator->errors()->toArray()
            );
        }

        DB::beginTransaction();
        try {
            $eventOrganizer = $this->eventOrganizer
                ->where('uuid', $uuid)
                ->where('user_id', Auth::id())
                ->first();

            if (!$eventOrganizer) {
                return MessageResponseJson::notFound(
                    'Event organizer not found or unauthorized'
                );
            }

            if ($eventOrganizer->application_status !== 'rejected') {
                return MessageResponseJson::forbidden(
                    'You can only resubmit a rejected application'
                );
            }

            $updateData = [
                'organization_name'       => $request->organization_name,
                'description'             => $request->description,
                'website'                 => $request->website,
                'instagram'               => $request->instagram,
                'twitter'                 => $request->twitter,
                'facebook'                => $request->facebook,
                'address'                 => $request->address,
                'city'                    => $request->city,
                'province'                => $request->province,
                'postal_code'             => $request->postal_code,
                'contact_person'          => $request->contact_person,
                'contact_phone'           => $request->contact_phone,
                'contact_email'           => $request->contact_email,
                'bank_name'               => $request->bank_name,
                'bank_account_number'     => $request->bank_account_number,
                'bank_account_name'       => $request->bank_account_name,
                'application_fee'         => SettingService::get('application_fee_event_organizer'),
                'security_deposit'        => $request->security_deposit,
                'application_status'      => 'pending',
                'verification_status'     => 'pending',
                'rejection_reason'        => null,
                'reviewed_by'             => null,
                'reviewed_at'             => null,
                'application_submitted_at' => now(),
            ];

            if ($request->hasFile('logo')) {
                if ($eventOrganizer->logo) {
                    Storage::disk('public')->delete($eventOrganizer->logo);
                }
                $updateData['logo'] = $request->file('logo')->store('event-organizers/logos', 'public');
            }

            if ($request->hasFile('banner')) {
                if ($eventOrganizer->banner) {
                    Storage::disk('public')->delete($eventOrganizer->banner);
                }
                $updateData['banner'] = $request->file('banner')->store('event-organizers/banners', 'public');
            }

            $updateData['required_documents'] = $request->filled('required_documents')
                ? json_encode($request->required_documents)
                : $eventOrganizer->required_documents;

            $uploadedDocuments = json_decode($eventOrganizer->uploaded_documents, true) ?? [];

            if ($request->hasFile('uploaded_documents')) {
                foreach ($uploadedDocuments as $oldDoc) {
                    Storage::disk('public')->delete($oldDoc);
                }
                $uploadedDocuments = [];

                $user = Auth::user();
                $userIdentifier = Str::before($user->email, '@');

                foreach ($request->file('uploaded_documents') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $fileName = pathinfo($originalName, PATHINFO_FILENAME)
                        . '_'
                        . $userIdentifier
                        . '_'
                        . now()->format('YmdHis')
                        . '.'
                        . $file->getClientOriginalExtension();

                    $path = $file->storeAs(
                        'event-organizers/documents',
                        $fileName,
                        'public'
                    );

                    $uploadedDocuments[] = $path;
                }
                $updateData['uploaded_documents'] = json_encode($uploadedDocuments);
            } else {
                $updateData['uploaded_documents'] = $eventOrganizer->uploaded_documents;
            }

            if ($request->filled('rejection_reason_response')) {
                $updateData['rejection_reason_response'] = $request->rejection_reason_response;
            }

            $eventOrganizer->update($updateData);

            DB::commit();

            return MessageResponseJson::success(
                'Event organizer application resubmitted successfully',
                $eventOrganizer->fresh()->load('user:id,name,email')
            );
        } catch (\Throwable $th) {
            DB::rollBack();

            if (isset($updateData['logo'])) {
                Storage::disk('public')->delete($updateData['logo']);
            }
            if (isset($updateData['banner'])) {
                Storage::disk('public')->delete($updateData['banner']);
            }
            if (isset($updateData['uploaded_documents'])) {
                $newDocs = json_decode($updateData['uploaded_documents'], true);
                foreach ($newDocs as $doc) {
                    Storage::disk('public')->delete($doc);
                }
            }

            return MessageResponseJson::serverError(
                'Failed to resubmit event organizer application',
                ['error' => $th->getMessage()]
            );
        }
    }
    public function regeneratePaymentInvoice(Request $request, string $uuid): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string|exists:payment_methods,code',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(
                'Validation failed',
                $validator->errors()->toArray()
            );
        }

        DB::beginTransaction();
        try {
            $eventOrganizer = $this->eventOrganizer
                ->where('uuid', $uuid)
                ->where('user_id', Auth::id())
                ->first();

            if (!$eventOrganizer) {
                return MessageResponseJson::notFound(
                    'Event organizer not found or unauthorized'
                );
            }

            $allowedStatuses = ['pending', 'expired', 'failed', 'unpaid'];
            if (!in_array($eventOrganizer->payment_status, $allowedStatuses)) {
                return MessageResponseJson::forbidden(
                    'Cannot regenerate invoice for this application'
                );
            }

            $applicationFee = SettingService::get('application_fee_event_organizer');
            $paymentMethod = $this->paymentService->getPaymentMethod($request->payment_method);

            if (!$paymentMethod) {
                return MessageResponseJson::badRequest('Payment method is not available');
            }

            $paymentFee = $this->paymentService->calculatePaymentFee($request->payment_method, $applicationFee);
            $totalAmount = $applicationFee + $paymentFee;

            $invoiceData = [
                'external_id' => 'EO-Application-' . $eventOrganizer->uuid,
                'amount' => $totalAmount,
                'description' => "Event Organizer Application Fee - {$eventOrganizer->organization_name}",
                'currency' => 'IDR',
                'customer' => [
                    'given_names' => $eventOrganizer->organization_name,
                    'email' => $eventOrganizer->user->email,
                    "mobile_number" => $eventOrganizer->contact_phone,
                    "addresses" => [
                        [
                            "city" => $eventOrganizer->city,
                            "country" => "Indonesia",
                            "postal_code" => $eventOrganizer->postal_code,
                            "state" => $eventOrganizer->province,
                        ]
                    ]
                ],
                'payment_methods' => [$request->payment_method],
                "success_redirect_url" => $request->success_redirect_url,
                "failure_redirect_url" => $request->failure_redirect_url,
            ];

            $invoiceResult = $this->paymentProvider->createInvoice($invoiceData);

            // Update payment reference dan status
            $eventOrganizer->update([
                'payment_reference' => $invoiceResult['id'],
                'payment_status' => 'pending', // Tambahkan kolom payment_status di migration
            ]);

            DB::commit();

            return MessageResponseJson::success('Payment invoice regenerated', [
                'payment_url' => $invoiceResult['invoice_url'],
                'invoice_id' => $invoiceResult['id'],
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod->name,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError(
                'Failed to regenerate payment invoice',
                ['error' => $th->getMessage()]
            );
        }
    }

    public function getBySlug(string $slug): JsonResponse
    {
        try {
            $eventOrganizer = $this->eventOrganizer
                ->with('user:id,name')
                ->where('organization_slug', $slug)
                ->where('verification_status', 'verified')
                ->where('status', $this->eventOrganizer::STATUS_ACTIVE)
                ->first();

            if (!$eventOrganizer) {
                return MessageResponseJson::notFound(
                    'Event organizer not found or not verified'
                );
            }

            $eventOrganizer->makeHidden([
                'bank_name',
                'bank_account_number',
                'bank_account_name',
                'uploaded_documents',
                'required_documents',
                'contact_phone',
                'contact_email',
                'application_fee',
                'security_deposit',
                'verification_notes',
                'rejection_reason'
            ]);

            return MessageResponseJson::success(
                'Event organizer profile retrieved successfully',
                $eventOrganizer
            );
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError(
                'Failed to retrieve event organizer profile',
                ['error' => $th->getMessage()]
            );
        }
    }

    public function getMyStatus(): JsonResponse
    {
        try {
            $eventOrganizer = $this->eventOrganizer
                ->with(['user:id,name,email', 'reviewedBy:id,name'])
                ->where('user_id', Auth::id())
                ->first();

            if (!$eventOrganizer) {
                return MessageResponseJson::success(
                    'No event organizer application found',
                    ['has_application' => false]
                );
            }

            return MessageResponseJson::success(
                'Event organizer status retrieved successfully',
                [
                    'has_application' => true,
                    'organizer' => [
                        ...$eventOrganizer->toArray(),
                        'uuid' => $eventOrganizer->uuid
                    ]
                ]
            );
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError(
                'Failed to retrieve event organizer status',
                ['error' => $th->getMessage()]
            );
        }
    }

    private function checkProfileCompleteness($user): ?array
    {
        $incompleteFields = [];

        $requiredFields = [
            'phone' => 'Phone number',
            'birth_date' => 'Birth date',
            'gender' => 'Gender',
            'address' => 'Address',
            'city' => 'City',
            'province' => 'Province',
            'postal_code' => 'Postal code',
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($user->$field)) {
                $incompleteFields[] = $label;
            }
        }

        return $incompleteFields ? $incompleteFields : null;
    }
}
