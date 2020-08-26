import $ from 'jquery'
import FormUploadGalleryBorder from '../../../../../upload-bundle/Resources/public/js/component/_upload_gallery'

const LoadUpload = () => {
    new Upload('user_messenger_conversation_message_upload', function (response) {
        let gallery = document.querySelector('.form-upload-gallery')

        let formHtml = document.createElement('div')
        formHtml.innerHTML = response.formHtml

        let firstUserMedia = formHtml.querySelector('.form-upload-gallery-entry')
        let input = firstUserMedia.querySelector('input[type="checkbox"]')
        input.checked = true

        gallery.prepend(firstUserMedia)

        FormUploadGalleryBorder(gallery)
    })
}

$(document).ready(function () {
    let user_messenger_conversation_message_upload = document.querySelector('#user_messenger_conversation_message_upload')

    if (null !== user_messenger_conversation_message_upload) {
        document.addEventListener('click', (e) => {
            let toggleBtn = e.target.closest('.user-messenger-toggle-user-media')

            if (null !== toggleBtn) {
                e.preventDefault()

                toggleBtn.classList.add('d-none')
                document.querySelector('#dropzone-user_messenger_conversation_message_upload').classList.remove('d-none')
                document.querySelector('.form-upload-gallery').parentNode.classList.remove('d-none')
            }
        }, false)

        LoadUpload()
    }
})

export default LoadUpload
