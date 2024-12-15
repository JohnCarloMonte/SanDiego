const form = document.querySelector('#messageForm');

function sendMessage(event) {
    event.preventDefault();


    const apikey = document.querySelector('#apiKey').value;
    const number = document.querySelector('#number').value;
    const message = document.querySelector('#message').value;

    const parameters = {
        apikey,
        number,
        message,
    }

    fetch('https://api.semaphore.co/api/v4/messages', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams(parameters),
        mode: 'no-cors'
    }).then(() => console.log('Message sent!'))
    .catch(error => console.log(error));
    form.reset();
}

form.addEventListener('submit', sendMessage);