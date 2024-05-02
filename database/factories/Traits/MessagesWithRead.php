<?php

namespace Database\Factories\Traits;

trait MessagesWithRead
{
    /**
     * Indicate that the message should be marked as read.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withRead()
    {
        return $this->state(function (array $attributes) {
            return [
                'read_at' => now(), // Set read_at to the current time
            ];
        });
    }
}
