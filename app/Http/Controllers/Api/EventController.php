<?php

namespace App\Http\Controllers\Api;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventOrganizer;
use App\Models\EventTag;
use App\Models\Tag;
use App\Rules\ValidateStatus;
use App\Services\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventController extends Controller
{
    protected $event, $eventOrganizer, $tag, $eventTag;

    public function __construct()
    {
        $this->event = new Event();
        $this->eventOrganizer = new EventOrganizer();
        $this->tag = new Tag();
        $this->eventTag = new EventTag();
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = $this->event->with(['organizer:id,organization_name,organization_slug,logo', 'category:id,name', 'tags:id,name']);

            foreach (['status', 'category_id', 'organizer_id', 'type'] as $filter) {
                if ($request->filled($filter)) {
                    $query->where($filter, $request->$filter);
                }
            }

            if ($request->filled('is_featured')) {
                $query->where('is_featured', $request->boolean('is_featured'));
            }

            if ($request->filled('start_date')) {
                $query->whereDate('start_datetime', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->whereDate('end_datetime', '<=', $request->end_date);
            }

            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('title', 'like', "%{$request->search}%")
                        ->orWhere('description', 'like', "%{$request->search}%");
                });
            }

            foreach (['city' => 'venue_city', 'province' => 'venue_province'] as $param => $field) {
                if ($request->filled($param)) {
                    $query->where($field, 'like', "%{$request->$param}%");
                }
            }

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $allowedSorts = ['created_at', 'start_datetime', 'title', 'views_count'];

            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $events = $query->paginate($request->get('per_page', 15));

            return MessageResponseJson::paginated('Events retrieved successfully', $events);
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to retrieve events', [$th->getMessage()]);
        }
    }

    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'category_id'           => 'required|exists:categories,id',
            'title'                 => 'required|string|max:255',
            'description'           => 'required|string',
            'terms_conditions'      => 'nullable|string',
            'banner_image'          => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery_images'        => 'nullable|array',
            'gallery_images.*'      => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'type'                  => 'required|in:online,offline,hybrid',
            'venue_name'            => 'required_if:type,offline,hybrid|nullable|string|max:255',
            'venue_address'         => 'required_if:type,offline,hybrid|nullable|string',
            'venue_city'            => 'required_if:type,offline,hybrid|nullable|string|max:255',
            'venue_province'        => 'required_if:type,offline,hybrid|nullable|string|max:255',
            'venue_latitude'        => 'nullable|string',
            'venue_longitude'       => 'nullable|string',
            'online_platform'       => 'required_if:type,online,hybrid|nullable|string|max:255',
            'online_link'           => 'required_if:type,online,hybrid|nullable|url',
            'start_datetime'        => 'required|date|after:now',
            'end_datetime'          => 'required|date|after:start_datetime',
            'registration_start'    => 'required|date|before:start_datetime',
            'registration_end'      => 'required|date|after:registration_start|before:start_datetime',
            'min_age'               => 'nullable|integer|min:1|max:100',
            'max_age'               => 'nullable|integer|min:1|max:100|gte:min_age',
            'is_featured'           => 'boolean',
            'tags'                  => 'nullable|array',
            'tags.*'                => 'string|max:255',
            'status'                => ['nullable', new ValidateStatus('eventStatus')],
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(errors: $validator->errors()->toArray());
        }

        try {
            $organizer = $this->eventOrganizer->findOrFail(Auth::user()->eventOrganizer->id);

            if ($organizer->status !== $this->eventOrganizer::STATUS_ACTIVE || $organizer->verification_status !== 'verified' || $organizer->application_status !== 'approved') {
                return MessageResponseJson::unauthorized("Event organizer tidak valid untuk membuat event.");
            }

            $baseSlug = Str::slug($request->title);
            $slug = $baseSlug;
            $counter = 1;
            while ($this->event->where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $bannerPath = $request->file('banner_image')->store('events/banners', 'public');
            $galleryPaths = [];
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $image) {
                    $galleryPaths[] = $image->store('events/gallery', 'public');
                }
            }

            $data = $request->except('tags');
            $data['organizer_id'] = $organizer->id;
            $data['slug'] = $slug;
            $data['banner_image'] = $bannerPath;
            $data['gallery_images'] = $galleryPaths ? json_encode($galleryPaths) : null;
            $data['status'] = $this->event::STATUS_DRAFT;
            $data['is_featured'] = $request->boolean('is_featured');
            $data['views_count'] = 0;
            $data['status'] = $request->input('status', 1);

            $event = $this->event->create($data);

            if ($request->filled('tags')) {
                $tagIds = collect($request->tags)->map(function ($tagName) {
                    return Tag::firstOrCreate(['name' => $tagName, 'slug' => Str::slug($tagName)])->id;
                });

                $tagData = $tagIds->map(function ($tagId) use ($event) {
                    return [
                        'event_id' => $event->id,
                        'tag_id' => $tagId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();
                EventTag::insert($tagData);
            }

            DB::commit();

            $event->load(['organizer:id,organization_name,organization_slug,logo', 'category:id,name', 'tags:id,name']);

            return MessageResponseJson::created('Event created successfully', $event);
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to create event', [$th->getMessage()]);
        }
    }

    public function show($slug): JsonResponse
    {
        try {
            $event = $this->event->with([
                'organizer:id,organization_name,organization_slug,logo,description,website,instagram,twitter,facebook,contact_person,contact_phone,contact_email',
                'category:id,name',
                'tags:id,name'
            ])->where('slug', $slug)->orWhere('id', $slug)->first();

            if (!$event) {
                return MessageResponseJson::notFound('Event not found');
            }

            $event->increment('views_count');

            return MessageResponseJson::success('Event retrieved successfully', $event);
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to retrieve event', [$th->getMessage()]);
        }
    }

    public function update(Request $request, $slug): JsonResponse
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'category_id'           => 'sometimes|exists:categories,id',
            'title'                 => 'sometimes|string|max:255',
            'description'           => 'sometimes|string',
            'terms_conditions'      => 'nullable|string',
            'banner_image'          => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery_images'        => 'nullable|array',
            'gallery_images.*'      => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'type'                  => 'sometimes|in:online,offline,hybrid',
            'venue_name'            => 'required_if:type,offline,hybrid|nullable|string|max:255',
            'venue_address'         => 'required_if:type,offline,hybrid|nullable|string',
            'venue_city'            => 'required_if:type,offline,hybrid|nullable|string|max:255',
            'venue_province'        => 'required_if:type,offline,hybrid|nullable|string|max:255',
            'venue_latitude'        => 'nullable|numeric|between:-90,90',
            'venue_longitude'       => 'nullable|numeric|between:-180,180',
            'online_platform'       => 'required_if:type,online,hybrid|nullable|string|max:255',
            'online_link'           => 'required_if:type,online,hybrid|nullable|url',
            'start_datetime'        => 'sometimes|date|after:now',
            'end_datetime'          => 'sometimes|date|after:start_datetime',
            'registration_start'    => 'sometimes|date|before:start_datetime',
            'registration_end'      => 'sometimes|date|after:registration_start|before:start_datetime',
            'min_age'               => 'nullable|integer|min:1|max:100',
            'max_age'               => 'nullable|integer|min:1|max:100|gte:min_age',
            'is_featured'           => 'boolean',
            'tags'                  => 'nullable|array',
            'tags.*'                => 'string|max:255',
            'status'                => ['nullable', new ValidateStatus('eventStatus')],
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(errors: $validator->errors()->toArray());
        }

        try {
            $event = $this->event->where(function ($q) use ($slug) {
                $q->where('slug', $slug)
                    ->orWhere('id', is_numeric($slug) ? (int) $slug : 0);
            })->first();

            if (!$event) {
                return MessageResponseJson::notFound('Event not found');
            }

            $data = $request->except('tags');

            if ($request->filled('title') && $request->title !== $event->title) {
                $baseSlug = Str::slug($request->title);
                $slug = $baseSlug;
                $counter = 1;
                while ($this->event->where('slug', $slug)->where('id', '!=', $event->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }
                $data['slug'] = $slug;
            }

            if ($request->hasFile('banner_image')) {
                Storage::disk('public')->delete($event->banner_image);
                $data['banner_image'] = $request->file('banner_image')->store('events/banners', 'public');
            }

            if ($request->hasFile('gallery_images')) {
                foreach (json_decode($event->gallery_images ?? '[]', true) as $img) {
                    Storage::disk('public')->delete($img);
                }
                $galleryPaths = [];
                foreach ($request->file('gallery_images') as $image) {
                    $galleryPaths[] = $image->store('events/gallery', 'public');
                }
                $data['gallery_images'] = json_encode($galleryPaths);
            }

            $data['is_featured'] = $request->boolean('is_featured', $event->is_featured);
            $data['status'] = $request->status ?? $event->status;

            $event->update($data);

            if ($request->filled('tags')) {
                EventTag::where('event_id', $event->id)->delete();

                $tagIds = collect($request->tags)->map(function ($tagName) {
                    return Tag::firstOrCreate(['name' => $tagName, 'slug' => Str::slug($tagName)])->id;
                });

                $tagData = $tagIds->map(function ($tagId) use ($event) {
                    return [
                        'event_id' => $event->id,
                        'tag_id' => $tagId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();

                EventTag::insert($tagData);
            }

            DB::commit();

            $event->load(['organizer:id,organization_name,organization_slug,logo', 'category:id,name', 'tags:id,name']);

            return MessageResponseJson::success('Event updated successfully', $event);
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to update event', [$th->getMessage()]);
        }
    }

    public function destroy($slug): JsonResponse
    {
        DB::beginTransaction();

        try {
            $event = $this->event->where(function ($q) use ($slug) {
                $q->where('slug', $slug)
                    ->orWhere('id', is_numeric($slug) ? (int) $slug : 0);
            })->first();

            if (!$event) {
                return MessageResponseJson::notFound('Event not found');
            }

            if ($event->status === $this->event::STATUS_COMPLETED) {
                return MessageResponseJson::unprocessable('Event dengan status COMPLETED tidak dapat dihapus.');
            }

            Storage::disk('public')->delete($event->banner_image);
            foreach (json_decode($event->gallery_images ?? '[]', true) as $img) {
                Storage::disk('public')->delete($img);
            }

            $event->delete();

            DB::commit();

            return MessageResponseJson::success('Event deleted successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to delete event', [$th->getMessage()]);
        }
    }

    public function getByOrganizer(Request $request): JsonResponse
    {
        try {
            $organizer = $this->eventOrganizer->where('id', Auth::user()->eventOrganizer->id)->first();

            if (!$organizer) {
                return MessageResponseJson::notFound('Organizer not found');
            }

            $events = $this->event->with(['category:id,name', 'tags:id,name'])
                ->where('organizer_id', $organizer->id)
                ->orderByDesc('created_at')
                ->paginate(15);

            return MessageResponseJson::success('Events retrieved successfully', [
                'organizer' => $organizer->only(['id', 'organization_name', 'organization_slug']),
                'data' => $events
            ]);
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to retrieve events', [$th->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $slug): JsonResponse
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'status' => ['required', new ValidateStatus('eventStatus')],
        ]);

        if ($validator->fails()) {
            return MessageResponseJson::validationError(errors: $validator->errors()->toArray());
        }

        try {
            $event = $this->event->where(function ($q) use ($slug) {
                $q->where('slug', $slug)
                    ->orWhere('id', is_numeric($slug) ? (int) $slug : 0);
            })->first();

            if (!$event) {
                return MessageResponseJson::notFound('Event not found');
            }

            if (now()->greaterThanOrEqualTo($event->end_datetime)) {
                return MessageResponseJson::unprocessable('Tidak dapat mengubah status karena event telah berakhir.');
            }

            $event->update(['status' => $request->status]);

            DB::commit();

            return MessageResponseJson::success('Event status updated successfully', $event);
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageResponseJson::serverError('Failed to update event status', [$th->getMessage()]);
        }
    }

    public function toggleFeatured($id): JsonResponse
    {
        try {
            $event = $this->event->findOrFail($id);

            $event->update(['is_featured' => !$event->is_featured]);

            return MessageResponseJson::success('Event featured status updated successfully', $event);
        } catch (\Throwable $th) {
            return MessageResponseJson::serverError('Failed to update featured status', [$th->getMessage()]);
        }
    }
}
