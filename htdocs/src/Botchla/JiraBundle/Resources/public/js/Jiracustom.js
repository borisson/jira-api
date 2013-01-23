$(document).ready(function () {
  $('.worklogTitle').on('mouseenter', function () {
    $(this).parent().find('.worklogcomment').addClass('active');
  }).on('mouseleave', function () {
    $(this).parent().find('.worklogcomment').removeClass('active');
  })
});