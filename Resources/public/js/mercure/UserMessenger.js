export const EventSourceListener = (eventSource) => {
    eventSource.addEventListener('user_messenger_replace_uuid', (e) => {
        let data = JSON.parse(e.data)
        let container = document.querySelector('#message-user-messenger > .comments[id="user-messenger-conversation-' + data.tmpUuid +'"]')

        if (null !== container) {
            container.id = 'user-messenger-conversation-' + data.uuid

            let deleteLinkHtml = document.createElement('div')
            deleteLinkHtml.innerHTML = data.delete_link

            container.appendChild(deleteLinkHtml.children[0])
        }
    }, false)

    eventSource.addEventListener('user_messenger_add_in_index_page', (e) => {
        let data = JSON.parse(e.data)
        let container = document.querySelector('#index-user-messenger')

        if (null !== container) {
            let alert = container.querySelector('.alert')
            let findIndexConversation = container.querySelector('.comment.user-messenger-conversation-' + data.uuid)

            if (null !== alert) {
                container = document.createElement('div')
                container.classList.add('comments')

                alert.replaceWith(container)
            } else {
                container = container.querySelector('.comments')
            }

            if (null !== findIndexConversation) {
                container.removeChild(findIndexConversation)
            }

            let messageHtml = document.createElement('div')
            messageHtml.innerHTML = data.messageHtml

            let findTitleDate = container.querySelector('h2.message-title[data-title-user-messenger-date="' + messageHtml.children[0].getAttribute('data-title-user-messenger-date') + '"]')

            if (null === findTitleDate) {
                container.insertBefore(messageHtml.children[1], container.firstChild)
                container.insertBefore(messageHtml.children[0], container.firstChild)
            } else {
                container.insertBefore(messageHtml.children[1], findTitleDate.nextSibling)
            }
        }
    }, false)

    eventSource.addEventListener('user_messenger_add_in_message_page', (e) => {
        let data = JSON.parse(e.data)
        let container = document.querySelector('#message-user-messenger > .comments[id="user-messenger-conversation-' + data.uuid +'"]')

        if (null !== container) {
            let messageHtml = document.createElement('div')
            messageHtml.innerHTML = data.messageHtml

            let findTitleDate = container.querySelector('h2.message-title[data-title-user-messenger-date="' + messageHtml.children[0].getAttribute('data-title-user-messenger-date') + '"]')

            if (null === findTitleDate) {
                container.appendChild(messageHtml.children[0])
                container.appendChild(messageHtml.children[0])

            } else {
                container.appendChild(messageHtml.children[1])
            }
        }
    }, false)

    eventSource.addEventListener('user_messenger_add_in_navbar', (e) => {
        let data = JSON.parse(e.data)
        let container = document.querySelector('.user-messenger-dropdown-menu')

        if (null !== container) {
            let findNavbarConversation = container.querySelector('.user-messenger-dropdown-item.user-messenger-conversation-' + data.uuid)

            if (null !== findNavbarConversation) {
                container.removeChild(findNavbarConversation)
            }

            let messageHtml = document.createElement('div')
            messageHtml.innerHTML = data.messageHtml

            container.insertBefore(messageHtml.children[0], container.firstChild)

            let emptyMessage = container.querySelector('.dropdown-empty-message')

            if (null !== emptyMessage) {
                container.removeChild(emptyMessage)
            }

            let goMessage = container.querySelector('.dropdown-go-message')

            if (null === goMessage) {
                goMessage = document.createElement('a')
                goMessage.classList.add('dropdown-item', 'dropdown-go-message')
                goMessage.setAttribute('href', container.getAttribute('data-go-message-url'))
                goMessage.innerText = container.getAttribute('data-go-message')

                container.appendChild(goMessage)
            }
        }
    }, false)

    eventSource.addEventListener('user_messenger_delete', (e) => {
        let data = JSON.parse(e.data)

        /*
            Index page
         */
        let indexContainer = document.querySelector('#index-user-messenger')

        if (null !== indexContainer) {
            let indexMessagesContainer = indexContainer.querySelector('.comments')
            let findIndexConversation = indexMessagesContainer.querySelector('.comment.user-messenger-conversation-' + data.uuid)

            if (null !== findIndexConversation) {
                indexMessagesContainer.removeChild(findIndexConversation)
            }

            let indexLength = indexContainer.querySelectorAll('.comment').length

            if (0 === indexLength) {
                indexContainer.removeChild(indexMessagesContainer)

                let indexAlert = document.createElement('div')
                indexAlert.classList.add('alert', 'alert-danger', 'mb-0')

                let indexAlertP = document.createElement('p')
                indexAlertP.classList.add('mb-0')
                indexAlertP.innerText = indexContainer.getAttribute('data-empty-message')

                indexAlert.appendChild(indexAlertP)
                indexContainer.appendChild(indexAlert)
            }
        }

        /*
            Navbar
         */
        let navbarContainer = document.querySelector('.user-messenger-dropdown-menu')

        if (null !== navbarContainer) {
            let findNavbarConversation = navbarContainer.querySelector('.user-messenger-dropdown-item.user-messenger-conversation-' + data.uuid)

            if (null !== findNavbarConversation) {
                navbarContainer.removeChild(findNavbarConversation)
            }

            let navbarLength = navbarContainer.querySelectorAll('.user-messenger-dropdown-item').length

            if (0 === navbarLength) {
                let emptyMessage = navbarContainer.querySelector('.dropdown-empty-message')
                console.log(emptyMessage)

                if (null === emptyMessage) {
                    emptyMessage = document.createElement('span')
                    emptyMessage.classList.add('dropdown-item', 'dropdown-empty-message')
                    emptyMessage.innerText = navbarContainer.getAttribute('data-empty-message')
                    console.log(emptyMessage)

                    navbarContainer.appendChild(emptyMessage)
                }

                let goMessage = navbarContainer.querySelector('.dropdown-go-message')

                if (null !== goMessage) {
                    navbarContainer.removeChild(goMessage)
                }
            }
        }

        /*
            Message page
         */
        let messageContainer = document.querySelector('#message-user-messenger > .comments[id="user-messenger-conversation-' + data.uuid +'"]')

        if (null !== messageContainer) {
            let messageElements = document.querySelectorAll('h2.message-title, .comment')

            messageElements.forEach((element) => {
                messageContainer.removeChild(element)
            })
        }
    }, false)

    eventSource.addEventListener('user_messenger_read', (e) => {
        let data = JSON.parse(e.data)

        let navbar = document.querySelector('.user-messenger-dropdown-menu')
        let elements = document.querySelectorAll('.user-messenger-conversation-' + data.uuid)

        elements.forEach((element) => {
            let badge = element.querySelector('.badge-read')

            if (null !== badge) {
                badge.innerText = navbar.getAttribute('data-label-read')

                if (badge.classList.contains('badge-danger')) {
                    badge.classList.replace('badge-danger', 'badge-success')
                }
            }
        })
    }, false)
}
