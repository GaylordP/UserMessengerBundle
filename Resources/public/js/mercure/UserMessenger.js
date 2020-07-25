import $ from 'jquery'

const IsBottomElementInViewport = (element) => {
    let rect = element.getBoundingClientRect()

    return (
        rect.left >= 0
            &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight)
            &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    )
}

const SendReadHttpRequest = () => {
    let container = document.querySelector('#message-user-messenger > .comments')

    let httpRequest = new XMLHttpRequest()
    httpRequest.open('GET', container.getAttribute('data-read-link'))
    httpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
    httpRequest.send()
}

function ReadMessageScroll()
{
    console.log('scroll')

    let newMessage = document.querySelectorAll('.comment')
    newMessage = newMessage[newMessage.length - 1]

    if (true === IsBottomElementInViewport(newMessage)) {
        console.log('LU !')
        window.removeEventListener('scroll', ReadMessageScroll)

        SendReadHttpRequest()
    }
}

const AddMessageInIndexPage = (uuid, messageHtml) => {
    let container = document.querySelector('#index-user-messenger')

    if (null !== container) {
        let alert = container.querySelector('.alert')
        let findIndexConversation = container.querySelector('.comment.user-messenger-conversation-' + uuid)

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

        let containerMessageHtml = document.createElement('div')
        containerMessageHtml.innerHTML = messageHtml

        let findTitleDate = container.querySelector('h2.message-title[data-title-user-messenger-date="' + containerMessageHtml.children[0].getAttribute('data-title-user-messenger-date') + '"]')

        $(containerMessageHtml.children[1]).find('[data-toggle="tooltip"]').tooltip()

        if (null === findTitleDate) {
            container.insertBefore(containerMessageHtml.children[1], container.firstChild)
            container.insertBefore(containerMessageHtml.children[0], container.firstChild)
        } else {
            container.insertBefore(containerMessageHtml.children[1], findTitleDate.nextSibling)
        }
    }
}

const AddMessageInNavbarPage = (uuid, messageHtml) => {
    let container = document.querySelector('.user-messenger-dropdown-menu')

    if (null !== container) {
        let findNavbarConversation = container.querySelector('.user-messenger-dropdown-item.user-messenger-conversation-' + uuid)

        if (null !== findNavbarConversation) {
            container.removeChild(findNavbarConversation)
        }

        let containerMessageHtml = document.createElement('div')
        containerMessageHtml.innerHTML = messageHtml

        container.insertBefore(containerMessageHtml.children[0], container.firstChild)

        let maxLength = 5
        let findNavbarConversations = container.querySelectorAll('.user-messenger-dropdown-item')

        if (findNavbarConversations.length > maxLength) {
            findNavbarConversations.forEach((conversation, index) => {
                if (index >= maxLength) {
                    container.removeChild(conversation)
                }
            })
        }

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
}

const AddMessageInMessagePage = (uuid, messageHtml, sender_or_recipient) => {
    let container = document.querySelector('#message-user-messenger > .comments[id="user-messenger-conversation-' + uuid +'"]')

    if (null !== container) {
        let containerMessageHtml = document.createElement('div')
        containerMessageHtml.innerHTML = messageHtml

        let findTitleDate = container.querySelector('h2.message-title[data-title-user-messenger-date="' + containerMessageHtml.children[0].getAttribute('data-title-user-messenger-date') + '"]')

        if (null === findTitleDate) {
            container.appendChild(containerMessageHtml.children[0])

            $(containerMessageHtml.children[0]).find('[data-toggle="tooltip"]').tooltip()

            container.appendChild(containerMessageHtml.children[0])
        } else {
            $(containerMessageHtml.children[1]).find('[data-toggle="tooltip"]').tooltip()

            container.appendChild(containerMessageHtml.children[1])
        }

        if ('recipient' === sender_or_recipient) {
            window.removeEventListener('scroll', ReadMessageScroll)

            let newMessage = container.querySelectorAll('.comment')
            newMessage = newMessage[newMessage.length - 1]

            if (true === IsBottomElementInViewport(newMessage)) {
                console.log('cas a');
                SendReadHttpRequest()
            } else {
                console.log('cas b')
                window.addEventListener('scroll', ReadMessageScroll)
            }
        }
    }
}

export const EventSourceListener = (eventSource) => {
    eventSource.addEventListener('user_messenger_replace_uuid', (e) => {
        let data = JSON.parse(e.data)
        let container = document.querySelector('#message-user-messenger > .comments[id="user-messenger-conversation-' + data.tmpUuid +'"]')

        if (null !== container) {
            container.id = 'user-messenger-conversation-' + data.uuid
            container.setAttribute('data-read-link', container.getAttribute('data-read-link').replace(data.tmpUuid, data.uuid))

            let deleteLinkHtml = document.createElement('div')
            deleteLinkHtml.innerHTML = data.delete_link

            container.appendChild(deleteLinkHtml.children[0])
        }
    }, false)

    eventSource.addEventListener('user_messenger_add', (e) => {
        let data = JSON.parse(e.data)

        AddMessageInIndexPage(data.uuid, data.index)
        AddMessageInNavbarPage(data.uuid, data.navbar)
        AddMessageInMessagePage(data.uuid, data.message, data.sender_or_recipient)
    }, false)

    eventSource.addEventListener('user_messenger_refresh_navbar', (e) => {
        let data = JSON.parse(e.data)
        let container = document.querySelector('.user-messenger-dropdown-menu')

        if (null !== container) {
            let findNavbarConversations = container.querySelectorAll('.user-messenger-dropdown-item')

            findNavbarConversations.forEach((conversation) => {
                container.removeChild(conversation)
            })

            data.messages.reverse().forEach((message) => {
                let messageHtml = document.createElement('div')
                messageHtml.innerHTML = message.html

                container.insertBefore(messageHtml.children[0], container.firstChild)
            })
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

            if (null !== indexMessagesContainer) {
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

                if (null === emptyMessage) {
                    emptyMessage = document.createElement('span')
                    emptyMessage.classList.add('dropdown-item', 'dropdown-empty-message')
                    emptyMessage.innerText = navbarContainer.getAttribute('data-empty-message')

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

    eventSource.addEventListener('user_messenger_unread_length', (e) => {
        let data = JSON.parse(e.data)

        let badge = document.querySelector('.badge-user-message-unread')
        let length = data.length
        let title = document.querySelector('title')
        let lastUnreadLength = parseInt(title.getAttribute('data-notification-unread-length')) + parseInt(title.getAttribute('data-message-unread-length'))

        title.setAttribute('data-message-unread-length', String(length))

        if (0 === length) {
            if (badge.classList.contains('badge-red')) {
                badge.classList.remove('badge-red')
                badge.classList.add('badge-secondary')
            }
        } else {
            if (badge.classList.contains('badge-secondary')) {
                badge.classList.remove('badge-secondary')
                badge.classList.add('badge-red')
            }
        }

        badge.innerText = length

        let nowUnreadLength = parseInt(title.getAttribute('data-notification-unread-length')) + parseInt(title.getAttribute('data-message-unread-length'))

        if (0 === nowUnreadLength) {
            if (0 !== lastUnreadLength) {
                title.innerHTML = title.innerHTML.replace('(' + String(lastUnreadLength) + ') ', '')
            }
        } else {
            if (0 === lastUnreadLength) {
                title.innerHTML = '(' + String(nowUnreadLength) + ') ' + title.innerHTML
            } else {
                title.innerHTML = title.innerHTML.replace('(' + String(lastUnreadLength) + ')', '(' + String(nowUnreadLength) + ')')
            }
        }
    }, false)
}
