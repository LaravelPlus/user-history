<?php

declare(strict_types=1);

namespace LaravelPlus\UserHistory\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'action' => $this->action,
            'description' => $this->description,
            'formatted_description' => $this->formatted_description,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'subject' => $this->whenLoaded('subject', fn () => [
                'id' => $this->subject->id,
                'name' => $this->subject->getActivitySubjectName(),
                'url' => $this->subject->getActivitySubjectUrl(),
            ]),
            'properties' => $this->properties,
            'metadata' => $this->metadata,
            'activity_icon' => $this->activity_icon,
            'activity_color' => $this->activity_color,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'created_at_formatted' => $this->created_at?->diffForHumans(),
            'created_at_date' => $this->created_at?->format('Y-m-d'),
            'created_at_time' => $this->created_at?->format('H:i:s'),
        ];
    }
}
