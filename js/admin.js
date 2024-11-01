jQuery(document).ready(function($) {
    // Make both lists sortable and connect them for drag-and-drop
    $('#all-posts-list, #selected-posts-list').sortable({
        connectWith: '.rssfeedticker-sortable',
        placeholder: 'sortable-placeholder',
        receive: function(event, ui) {
            updateSelectedPosts();
        },
        stop: function(event, ui) {
            updateSelectedPosts();
        }
    }).disableSelection();

    // Update hidden input field based on selected posts
    function updateSelectedPosts() {
        var selectedPosts = $('#selected-posts-list li').map(function() {
            return $(this).data('id');
        }).get();
        $('#rssfeedticker_selected_posts').val(selectedPosts.join(','));
    }
});
