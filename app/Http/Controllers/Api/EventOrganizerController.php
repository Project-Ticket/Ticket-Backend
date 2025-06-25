<?php

namespace App\Http\Controllers\Api;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\EventOrganizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventOrganizerController extends Controller
{
    protected $eventOrganizer;

    public function __construct()
    {
        $this->eventOrganizer = new EventOrganizer();
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
            $eventOrganizers = $query->with('user:id,name,email')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

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
        if (!Auth::user()->hasRole('User')) {
            return MessageResponseJson::forbidden(
                'Only users with "User" role can register as Event Organizer'
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
                foreach ($request->file('uploaded_documents') as $file) {
                    $uploadedDocuments[] = $file->store('event-organizers/documents', 'public');
                }
            }

            $eventOrganizer = $this->eventOrganizer->create([
                'uuid'                    => Str::uuid(),
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
                'application_fee'         => $request->application_fee,
                'security_deposit'        => $request->security_deposit,
                'required_documents'      => $request->required_documents ? json_encode($request->required_documents) : null,
                'uploaded_documents'      => !empty($uploadedDocuments) ? json_encode($uploadedDocuments) : null,
                'application_submitted_at' => now(),
                'verification_status'     => 'pending',
                'application_status'      => 'pending',
                'status'                  => 1,
            ]);

            DB::commit();

            return MessageResponseJson::created(
                'Event organizer application submitted successfully',
                $eventOrganizer->load('user:id,name,email')
            );
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

    public function getBySlug(string $slug): JsonResponse
    {
        try {
            $eventOrganizer = $this->eventOrganizer
                ->with('user:id,name')
                ->where('organization_slug', $slug)
                ->where('verification_status', 'verified')
                ->where('status', 1)
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
                    'organizer' => $eventOrganizer
                ]
            );
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError(
                'Failed to retrieve event organizer status',
                ['error' => $th->getMessage()]
            );
        }
    }
}
