$('.bt_getinvite').off('click').on('click', function() {

    console.log(invitebotdiscord);
    var win = window.open(invitebotdiscord, '_blank');
    win.focus();
});