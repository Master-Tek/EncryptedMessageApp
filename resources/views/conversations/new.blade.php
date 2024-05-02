<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="container mx-auto">
                    <div class="mb-6 flex flex-row items-end gap-5 justify-start">
                        <a href="{{route('conversations.index')}}" class="bg-blue-900 text-white hover:bg-blue-400 font-bold py-2 px-4 mt-3 rounded text-center">
                            <i class="fa fa-arrow-left"></i>
                        </a>
                        <div>
                            <label for="recipientSelect" class="block font-medium text-gray-700">Select User</label>
                            <select id="recipientSelect" class="border rounded p-2" style="width:200px;" onchange="Messages.checkConversationExists()">
                                <option value="" disabled selected>Select a user</option>
                                @foreach ($recipients as $recipient)
                                    <option value="{{$recipient->id}}" {{ $recipient->id == $currentUserId ? 'selected' : '' }}>{{ $recipient->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Message Window Content -->
                    <div class="bg-white p-4 rounded shadow">
                        <h1 class="text-xl font-bold mb-4">Message Window</h1>
                        
                        <!-- Display Messages -->
                        <div id="messageDisplay" class="mb-4 h-48 overflow-y-scroll border p-2 bg-gray-100 rounded">
                            <!-- Messages will be displayed here -->
                        </div>

                        <!-- Message Input -->
                        <div class="w-full">
                            <div class="relative">
                                <form onsubmit="Messages.sendMessage(event, true)">
                                    <input type="text" id="messageInput" placeholder="Write your message..." class="border border-gray-300 rounded-lg w-full px-4 py-2 focus:outline-none focus:border-blue-500">
                                    <button type="submit" class="absolute right-0 top-0 mt-2 mr-4 text-gray-600 hover:text-gray-800">
                                        <i class="fa fa-caret-right"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
