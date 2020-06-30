/*
const ScrollTo = (element, to, duration) => {
    if (duration <= 0) return;

    let difference = to - element.scrollTop
    let perTick = difference / duration * 10

    setTimeout(function() {
        element.scrollTop = element.scrollTop + perTick

        if (element.scrollTop === to) return

        scrollTo(element, to, duration - 10)
    }, 10);
}

 */

const SendForm = (form) => {
    let user_messenger_conversation_message_message = form.querySelector('#user_messenger_conversation_message_message')
    let user_messenger_conversation_message__token = form.querySelector('#user_messenger_conversation_message__token')

    let httpRequest = new XMLHttpRequest()
    httpRequest.open('POST', form.getAttribute('action'))
    httpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
    httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
    httpRequest.send(user_messenger_conversation_message_message.getAttribute('name') + '=' + encodeURIComponent(user_messenger_conversation_message_message.value) + '&' + user_messenger_conversation_message__token.getAttribute('name') + '=' + user_messenger_conversation_message__token.value)
    httpRequest.onreadystatechange = () => {
        if (
          httpRequest.readyState === XMLHttpRequest.DONE
            &&
          httpRequest.status === 200
        ) {
            let json = JSON.parse(httpRequest.responseText)

            if ('success' === json.status) {
                let errorsMessages = form.querySelectorAll('.invalid-feedback')

                errorsMessages.forEach((error) => {
                    error.remove()
                })

                let errorsFormClass = form.querySelectorAll('.form-control.is-invalid')

                errorsFormClass.forEach((error) => {
                    error.classList.remove('is-invalid')
                })

                form.reset()
            } else if ('form_error' === json.status) {
                let formHtml = document.createElement('div')
                formHtml.innerHTML = json.formHtml
                form.replaceWith(formHtml.firstChild)
            }
        }
    }
}

document.addEventListener('submit', (e) => {
    let form = e.target.closest('form[name="user_messenger_conversation_message"]')

    if (null !== form) {
        e.preventDefault()

        SendForm(form)
    }
}, false)

let form = document.querySelector('#user_messenger_conversation_message_message');

if (null !== form) {
    form.addEventListener('keypress', function(event) {
        if (event.keyCode == 13) {
            event.preventDefault()

            let form = document.querySelector('form[name="user_messenger_conversation_message"]')

            SendForm(form)
        }
    });
}
