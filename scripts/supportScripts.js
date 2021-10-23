function toggleDetails(id) {
    var rowID='#'+id;
    $(rowID).toggle();
}
function showAll() {
    $('.response').show();
    $('.show_all').innerHTML = 'Hide';
}