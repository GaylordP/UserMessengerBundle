import './component/_message_form_submit'
import './component/_message_navbar'
import './component/_message_user_media'
import './component/_message_user_media_upload'

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

/*

    Enter + Submit
    Fonctionne mais bug : shift + entrée ne doit pas soumettre le formulaire

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

 */