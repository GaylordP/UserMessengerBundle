document.addEventListener('submit', (e) => {
    let formMessage = e.target.closest('form[name="user_messenger_conversation_message"]')

    if (null !== formMessage) {
        e.preventDefault()

        let user_messenger_conversation_message_message = formMessage.querySelector('#user_messenger_conversation_message_message')
        let user_messenger_conversation_message__token = formMessage.querySelector('#user_messenger_conversation_message__token')

        let httpRequest = new XMLHttpRequest()
        httpRequest.open('POST', formMessage.getAttribute('action'))
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
                    let errorsMessages = formMessage.querySelectorAll('.invalid-feedback')

                    errorsMessages.forEach((error) => {
                        error.remove()
                    })

                    let errorsFormClass = formMessage.querySelectorAll('.form-control.is-invalid')

                    errorsFormClass.forEach((error) => {
                        error.classList.remove('is-invalid')
                    })

                    formMessage.reset()
                } else if ('form_error' === json.status) {
                    let formHtml = document.createElement('div')
                    formHtml.innerHTML = json.formHtml
                    formMessage.replaceWith(formHtml.firstChild)
                }
            }
        }
    }
}, false)
