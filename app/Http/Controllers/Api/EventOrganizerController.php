<?php

namespace App\Http\Controllers\Api;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\EventOrganizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventOrganizerController extends Controller
{
    protected $eventOrganizer;

    public function __construct()
    {
        $this->eventOrganizer = new EventOrganizer();
    }
    public function store(Request $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'organization_name'       => 'required|string|max:255',
            'description'             => 'nullable|string',
            'logo'                    => 'nullable|image|max:2048',
            'banner'                  => 'nullable|image|max:4096',
            'website'                 => 'nullable|url',
            'instagram'               => 'nullable|url',
            'twitter'                 => 'nullable|url',
            'facebook'                => 'nullable|url',
            'address'                 => 'required|string',
            'city'                    => 'required|string|max:100',
            'province'                => 'required|string|max:100',
            'postal_code'             => 'required|string|max:10',
            'contact_person'          => 'required|string|max:100',
            'contact_phone'           => 'required|string|max:20',
            'contact_email'           => 'required|email',
            'bank_name'               => 'nullable|string|max:100',
            'bank_account_number'     => 'nullable|string|max:50',
            'bank_account_name'       => 'nullable|string|max:100',
            'application_fee'         => 'nullable|numeric',
            'security_deposit'        => 'nullable|numeric',
            'required_documents'      => 'nullable|array',
            'uploaded_documents'      => 'nullable|array',
            'uploaded_documents.*'    => 'file|max:5120',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(
                errors: $validator->errors()->toArray()
            );
        }

        $existing = $this->eventOrganizer->where('user_id', Auth::id())->first();
        if ($existing) {
            return MessageResponseJson::conflict('You have already created an Event Organizer.');
        }

        $baseSlug = Str::slug($request->organization_name);
        $slug = $baseSlug;
        while ($this->eventOrganizer->where('organization_slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . mt_rand(100, 999);
        }

        $uploadedDocuments = [];
        if ($request->hasFile('uploaded_documents')) {
            foreach ($request->file('uploaded_documents') as $file) {
                $uploadedDocuments[] = $file->store('documents', 'public');
            }
        }

        try {
            $eventOrganizer = $this->eventOrganizer->create([
                'user_id'               => Auth::id(),
                'organization_name'     => $request->organization_name,
                'organization_slug'     => $slug,
                'description'           => $request->description,
                'logo'                  => $request->hasFile('logo') ? $request->file('logo')->store('logos', 'public') : null,
                'banner'                => $request->hasFile('banner') ? $request->file('banner')->store('banners', 'public') : null,
                'website'               => $request->website,
                'instagram'             => $request->instagram,
                'twitter'               => $request->twitter,
                'facebook'              => $request->facebook,
                'address'               => $request->address,
                'city'                  => $request->city,
                'province'              => $request->province,
                'postal_code'           => $request->postal_code,
                'contact_person'        => $request->contact_person,
                'contact_phone'         => $request->contact_phone,
                'contact_email'         => $request->contact_email,
                'bank_name'             => $request->bank_name,
                'bank_account_number'   => $request->bank_account_number,
                'bank_account_name'     => $request->bank_account_name,
                'application_fee'       => $request->application_fee,
                'security_deposit'      => $request->security_deposit,
                'required_documents'    => $request->required_documents ? json_encode($request->required_documents) : null,
                'uploaded_documents'    => $uploadedDocuments ? json_encode($uploadedDocuments) : null,
                'application_submitted_at' => now(),
            ]);

            DB::commit();

            return MessageResponseJson::success('Event Organizer created successfully', $eventOrganizer);
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to create Event Organizer', [$th->getMessage()]);
        }
    }

    public function show(string $uuid)
    {
        $eventOrganizer = $this->eventOrganizer
            ->where('uuid', $uuid)
            ->orWhere('id', $uuid)
            ->where('user_id', Auth::id())
            ->first();

        if (!$eventOrganizer) {
            return MessageResponseJson::notFound('Event Organizer not found or unauthorized.');
        }

        return MessageResponseJson::success('Event Organizer data retrieved successfully', $eventOrganizer);
    }

    public function update(Request $request, string $uuid)
    {
        DB::beginTransaction();

        $eventOrganizer = $this->eventOrganizer->where('uuid', $uuid)->where('user_id', Auth::id())->first();
        if (!$eventOrganizer) {
            return MessageResponseJson::notFound('Event Organizer not found or unauthorized.');
        }

        $validator = Validator::make($request->all(), [
            'organization_name'       => 'required|string|max:255',
            'description'             => 'nullable|string',
            'logo'                    => 'nullable|image|max:2048',
            'banner'                  => 'nullable|image|max:4096',
            'website'                 => 'nullable|url',
            'instagram'               => 'nullable|url',
            'twitter'                 => 'nullable|url',
            'facebook'                => 'nullable|url',
            'address'                 => 'required|string',
            'city'                    => 'required|string|max:100',
            'province'                => 'required|string|max:100',
            'postal_code'             => 'required|string|max:10',
            'contact_person'          => 'required|string|max:100',
            'contact_phone'           => 'required|string|max:20',
            'contact_email'           => 'required|email',
            'bank_name'               => 'nullable|string|max:100',
            'bank_account_number'     => 'nullable|string|max:50',
            'bank_account_name'       => 'nullable|string|max:100',
            'application_fee'         => 'nullable|numeric',
            'security_deposit'        => 'nullable|numeric',
            'required_documents'      => 'nullable|array',
            'uploaded_documents'      => 'nullable|array',
            'uploaded_documents.*'    => 'file|max:5120',
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(errors: $validator->errors()->toArray());
        }

        $uploadedDocuments = json_decode($eventOrganizer->uploaded_documents, true) ?? [];
        if ($request->hasFile('uploaded_documents')) {
            foreach ($request->file('uploaded_documents') as $file) {
                $uploadedDocuments[] = $file->store('documents', 'public');
            }
        }

        try {
            $eventOrganizer->update([
                'organization_name'     => $request->organization_name,
                'description'           => $request->description,
                'logo'                  => $request->hasFile('logo') ? $request->file('logo')->store('logos', 'public') : $eventOrganizer->logo,
                'banner'                => $request->hasFile('banner') ? $request->file('banner')->store('banners', 'public') : $eventOrganizer->banner,
                'website'               => $request->website,
                'instagram'             => $request->instagram,
                'twitter'               => $request->twitter,
                'facebook'              => $request->facebook,
                'address'               => $request->address,
                'city'                  => $request->city,
                'province'              => $request->province,
                'postal_code'           => $request->postal_code,
                'contact_person'        => $request->contact_person,
                'contact_phone'         => $request->contact_phone,
                'contact_email'         => $request->contact_email,
                'bank_name'             => $request->bank_name,
                'bank_account_number'   => $request->bank_account_number,
                'bank_account_name'     => $request->bank_account_name,
                'application_fee'       => $request->application_fee,
                'security_deposit'      => $request->security_deposit,
                'required_documents'    => $request->required_documents ? json_encode($request->required_documents) : null,
                'uploaded_documents'    => $uploadedDocuments ? json_encode($uploadedDocuments) : null,
            ]);

            DB::commit();

            return MessageResponseJson::success('Event Organizer updated successfully', $eventOrganizer);
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to update Event Organizer', [$th->getMessage()]);
        }
    }

    public function destroy(string $uuid)
    {
        $eventOrganizer = $this->eventOrganizer->where('uuid', $uuid)->where('user_id', Auth::id())->first();

        if (!$eventOrganizer) {
            return MessageResponseJson::notFound('Event Organizer not found or unauthorized.');
        }

        try {
            $eventOrganizer->delete();
            return MessageResponseJson::success('Event Organizer deleted successfully');
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to delete Event Organizer', [$th->getMessage()]);
        }
    }
}
