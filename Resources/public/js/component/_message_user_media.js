const CheckboxBorder = (userMedia, input) => {
    let thumbnail = userMedia.querySelector('.img-thumbnail')

    if (true === input.checked) {
        thumbnail.classList.add('bg-success', 'border-success')
    } else {
        thumbnail.classList.remove('bg-success', 'border-success')
    }
}

let userMedias = document.querySelectorAll('.user-messenger-form-user-media-entry')

userMedias.forEach((userMedia) => {
    let input = userMedia.querySelector('input[type="checkbox"]')

    CheckboxBorder(userMedia, input)
})

document.addEventListener('click', (e) => {
    let userMedia = e.target.closest('.user-messenger-form-user-media-entry')

    if (null !== userMedia) {
        e.preventDefault()

        let input = userMedia.querySelector('input[type="checkbox"]')

        if (true === input.checked) {
            input.checked = false
        } else {
            input.checked = true
        }

        CheckboxBorder(userMedia, input)
    }
}, false)

export default CheckboxBorder