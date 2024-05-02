import moment from 'moment';

// Function to send a message to a user
export const sendMessage = (event, newMessage = false, recipientId) => {
    event.preventDefault();

    const messageInput = document.getElementById('messageInput').value;
    if (messageInput.trim() === '') return;

    const selectedUserId = recipientId ?? document.getElementById('recipientSelect').value;

    if (!selectedUserId) {
        alert('Please select a recipient to send the message to.');
        return;
    } 

    fetch('/messages/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
            content: messageInput,
            receiver_id: selectedUserId,
        }),
    })
        .then(response => response.json())
        .then(() => {
            if (newMessage) {
                window.location.href = `/conversations/${selectedUserId}`;
            } else {
                loadConversation(recipientId); 
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
        });

    document.getElementById('messageInput').value = '';
}

// Function to check if a conversation exists with a user
export const checkConversationExists = () => {
    const selectedUserId = document.getElementById('recipientSelect').value;

    fetch(`/conversations/${selectedUserId}/conversation-exists`)
        .then((response) => response.json())
        .then((data) => {
            if (data.exists) {
                window.location.href = `/conversations/${selectedUserId}`;
            }
        });
}

/**
 * Function to load a conversation with a user
 * 
 * @param {*} recipientId 
 */
// Function to load a conversation with a user

export const loadConversation = (recipientId) => {
    const authUserId = document.getElementById('authUserId').value; // Use a hidden input to store auth user ID
    const selectedUserId = recipientId ?? document.getElementById('recipientSelect').value;

    fetch(`/messages?recipient_id=${selectedUserId}`)
        .then(response => response.json())
        .then(data => {
            const messageDisplay = document.getElementById('messageDisplay');
            messageDisplay.innerHTML = '';

            data.forEach(message => {
                const messageContainer = document.createElement('div');
                messageContainer.className = 'message-container justify-between rounded p-2 mb-2 flex items-center gap-4';

                const messageContentElement = document.createElement('div');
                messageContentElement.className = 'flex items-center';
                messageContentElement.textContent = message.content;
                messageContentElement.style = 'gap: 0.5rem;';

                const timeAgoElement = document.createElement('div');
                timeAgoElement.textContent = moment(message.created_at).fromNow();
                timeAgoElement.style = 'font-size: 0.7rem;font-style: italic;';

                if (message.sender_id === parseInt(authUserId)) {
                    const deleteButtonElement = document.createElement('i');
                    deleteButtonElement.className = 'fa fa-trash';
                    deleteButtonElement.style = 'font-size: 0.7rem; cursor: pointer; color: red;';
                    deleteButtonElement.addEventListener('click', (event) => {
                        event.stopPropagation();
                        softDeleteMessage(message.id);
                    });

                    messageContentElement.appendChild(deleteButtonElement);
                    messageContainer.className += ' bg-white';
                    messageContainer.style = 'flex-direction: row-reverse';
                } else {
                    messageContainer.className += ' bg-light-green';
                }

                messageContainer.appendChild(messageContentElement);
                messageContainer.appendChild(timeAgoElement);
                messageDisplay.appendChild(messageContainer);
            });

            messageDisplay.scrollTop = messageDisplay.scrollHeight;
        })
        .catch(error => {
            console.error('Error loading messages:', error);
        });
}

export const softDeleteMessage = (messageId) => {
    fetch(`/messages/${messageId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        }
    })
        .then(response => response.json())
        .then(response => {
            if (response.status === 'success') {
                if (response.exists) {
                    loadConversation(response.recipient_id);
                } else {
                    window.location.href = '/conversations';
                }
            } else {
                alert('Failed to delete the message.');
            }
        })
        .catch(error => {
            console.error('Error deleting message:', error);
        });
}

document.addEventListener('DOMContentLoaded', function () {
    const recipientId = document.getElementById('recipientId');
    if (recipientId)
        loadConversation(recipientId.value);
});
