$(document).ready(function()
{
    'use strict';

    var $current_amount = $('#kubithon-donations-amount');
    var $progress_bar = $('#kubithon-donations-bar');
    var $goals_list = $('#kubithon-donations-goals');
    var $streams_list = $('#live');

    setInterval(function()
    {
        $.ajax({
            'url': update_url,
            'success': function (data)
            {
                if ($goals_list.data('hash') !== data['goals_hash'])
                {
                    $current_amount.html(data['current_gain'] + '&nbsp;&euro;');
                    $progress_bar.html(data['current_gain'] + '&nbsp;&euro;');

                    $progress_bar.attr('value', data['current_gain']);
                    $progress_bar.attr('max', data['max_gain']);

                    $goals_list.html(data['goals_html']);
                    $goals_list.data('hash', data['goals_hash']);
                }

                if ($streams_list.data('hash') !== data['streams_hash'])
                {
                    $streams_list.html(data['streams_html']);
                    $streams_list.data('hash', data['streams_hash']);
                }
            }
        });
    }, 45000);
});
