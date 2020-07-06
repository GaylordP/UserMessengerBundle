import $ from 'jquery'
import CheckboxBorder from './_message_user_media'

$(document).ready(function () {
    let user_messenger_conversation_message_upload = document.querySelector('#user_messenger_conversation_message_upload')

    if (null !== user_messenger_conversation_message_upload) {
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
