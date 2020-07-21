import $ from 'jquery'

$(document).ready(function () {
    $('#navbarDropdownUserMessengerContainer').on('show.bs.dropdown', function () {
        $('#navbarDropdownUserMessengerContainer').tooltip('hide')
    })
})
