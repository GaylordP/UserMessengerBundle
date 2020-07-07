import $ from 'jquery'
import CheckboxBorder from './_message_user_media'

$(document).ready(function () {
    let user_messenger_conversation_message_upload = document.querySelector('#user_messenger_conversation_message_upload')

    if (null !== user_messenger_conversation_message_upload) {
        let toggleBtn = document.querySelector('.user-messenger-toggle-user-media')

        toggleBtn.onclick = function (e) {
            e.preventDefault()

            toggleBtn.classList.add('d-none')
            document.querySelector('#dropzone-user_messenger_conversation_message_upload').classList.remove('d-none')
            document.querySelector('.user-messenger-form-user-media').parentNode.classList.remove('d-none')
        }

        new Upload('user_messenger_conversation_message_upload', function (response) {
            let gallery = document.querySelector('.row.user-messenger-form-user-media')

            let formHtml = document.createElement('div')
            formHtml.innerHTML = response.formHtml

            let firstUserMedia = formHtml.querySelector('.user-messenger-form-user-media-entry')
            let input = firstUserMedia.querySelector('input[type="checkbox"]')
            input.checked = true

            CheckboxBorder(firstUserMedia, input)

            gallery.prepend(firstUserMedia)
        })
    }
})
