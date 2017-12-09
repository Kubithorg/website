$(document).ready(function()
{
    'use strict';

    var $current_amount = $('#kubithon-donations-amount');
    var $progress_bar = $('#kubithon-donations-bar');
    var $goals_list = $('#kubithon-donations-goals');

    setInterval(function()
    {
        $.ajax({
            'url': goals_update_url,
            'success': function (data, status)
            {
                if ($goals_list.data('hash') !== data['hash'])
                {
                    $current_amount.html(data['current_gain'] + '&nbsp;&euro;');
                    $progress_bar.html(data['current_gain'] + '&nbsp;&euro;');

                    $progress_bar.attr('value', data['current_gain']);
                    $progress_bar.attr('max', data['max_gain']);

                    $goals_list.html(data['goals_html']);
                    $goals_list.data('hash', data['hash']);
                }
            }
        });
    }, 60000);
});
