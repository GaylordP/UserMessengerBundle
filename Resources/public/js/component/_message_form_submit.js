import CheckboxBorder from './_message_user_media'

const SendForm = (form) => {
    let user_messenger_conversation_message_message = form.querySelector('#user_messenger_conversation_message_message')
    let user_messenger_conversation_message__token = form.querySelector('#user_messenger_conversation_message__token')
    let userMediasCheckboxes = document.getElementsByName('user_messenger_conversation_message[userMedias][]');
    let userMediasCheckboxesData = [];
    for (var i = 0; i < userMediasCheckboxes.length; i++) {
        if (userMediasCheckboxes[i].checked){
            userMediasCheckboxesData.push('user_messenger_conversation_message[userMedias][]=' + userMediasCheckboxes[i].value)
        }
    }

    let httpRequest = new XMLHttpRequest()
    httpRequest.open('POST', form.getAttribute('action'))
    httpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
    httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
    httpRequest.send('user_messenger_conversation_message[message]=' + encodeURIComponent(user_messenger_conversation_message_message.value) + '&' + userMediasCheckboxesData.join('&') + '&user_messenger_conversation_message[_token]=' + user_messenger_conversation_message__token.value)
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

                let userMedias = form.querySelectorAll('.user-messenger-form-user-media-entry')

                userMedias.forEach((userMedia) => {
                    let input = userMedia.querySelector('input[type="checkbox"]')
                    input.checked = false

                    CheckboxBorder(userMedia, input)
                })
            } else if ('form_error' === json.status) {
                let formHtml = document.createElement('div')
                formHtml.innerHTML = json.formHtml

                form.replaceWith(formHtml.firstChild)

                let userMedias = document.querySelectorAll('.user-messenger-form-user-media-entry')

                userMedias.forEach((userMedia) => {
                    let input = userMedia.querySelector('input[type="checkbox"]')

                    CheckboxBorder(userMedia, input)
                })
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
