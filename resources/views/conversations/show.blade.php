<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="container mx-auto">
                    <div class="mb-6 flex flex-row items-center gap-5 justify-start">
                        <a href="{{route('conversations.index')}}" class="bg-blue-900 text-white hover:bg-blue-400 font-bold py-2 px-4 mt-3 rounded text-center">
                            <i class="fa fa-arrow-left"></i>
                        </a>
                    </div>

                    <input type="hidden" id="authUserId" value="{{ auth()->id() }}">
                    <input type="hidden" id="recipientId" value="{{ $recipientId }}">

                    <!-- Message Window Content -->
                    <div class="bg-white p-4 rounded shadow">
                        <h1 class="text-xl font-bold mb-4">Message Window</h1>

                        <div id="messageDisplay" class="mb-4 h-96 overflow-y-scroll border p-2 bg-gray-100 rounded flex flex-col gap-5">
                            <!-- Empty wrapper -->
                        </div>

                        <!-- Message Input -->
                        <div class="w-full">
                            <div class="relative">
                                <form onsubmit="Messages.sendMessage(event, false, {{$recipientId}})">
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
