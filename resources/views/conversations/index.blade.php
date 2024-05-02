<x-app-layout>
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm mx-auto mt-10">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-lg font-semibold text-gray-900">Your messages</h1>
            <a href="{{route('conversations.new')}}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                Compose 
            </a>
        </div>
        {!! $conversations->isEmpty() ? '<p>No messages.</p>' : '' !!}
        <ul>
            @foreach ($conversations as $conversation)
                @php
                    $otherUser = $conversation->lastMessage->sender_id == $currentUserId ? $conversation->lastMessage->receiver : $conversation->lastMessage->sender;
                @endphp
                <li class="py-2 border-b border-gray-200">
                    <a href="{{route('conversations.show', ['id' => $otherUser->id])}}">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-800">{{ $otherUser->name }}</span>
                            <div>
                                <span class="text-sm text-gray-500">{{\App\Helpers\DateHelper::humanizeDate($conversation->lastMessage->created_at)}}</span>
                                <span class="bg-red-500 text-white rounded-full px-2 py-1 text-sm">{{ $conversation->unread_count }}</span>
                            </div>
                        </div>
                        <p class="text-gray-600 text-sm">{{ $conversation->lastMessage->content }}</p>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</x-app-layout>
