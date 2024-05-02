<?php

namespace Database\Factories;

use Database\Factories\Traits\MessagesWithRead;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    use MessagesWithRead;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'sender_id' => User::factory(),
            'receiver_id' => User::factory(),
            'content' => $this->faker->sentence,
            'read_at' => $this->faker->randomElement([null, now()]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

