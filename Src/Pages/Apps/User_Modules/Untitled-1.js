format: 'Y-m-d H:i',
                                                        timepicker: true,
                                                        datepicker: true,
                                                        step: 30,
                                                        theme: <?php echo $_SESSION['theme'] == 'dark' ? "'dark'" : "'light'"; ?> ,
                                                        lang: 'en',
                                                        showSecond: true,
                                                        scrollMonth: false,
                                                        scrollTime: false,
                                                        closeOnDateSelect: false,
                                                        closeOnTimeSelect: true,
                                                        mask: true,
                                                        minDate: '<?php echo date('Y-m-d H:i'); ?>',
                                                        allowTimes: [
                                                            '00:00', '00:30', '01:00', '01:30',
                                                            '02:00', '02:30', '03:00', '03:30',
                                                            '04:00', '04:30', '05:00', '05:30',
                                                            '06:00', '06:30', '07:00', '07:30',
                                                            '08:00', '08:30', '09:00', '09:30',
                                                            '10:00', '10:30', '11:00', '11:30',
                                                            '12:00', '12:30', '13:00', '13:30',
                                                            '14:00', '14:30', '15:00', '15:30',
                                                            '16:00', '16:30', '17:00', '17:30',
                                                            '18:00', '18:30', '19:00', '19:30',
                                                            '20:00', '20:30', '21:00', '21:30',
                                                            '22:00', '22:30', '23:00', '23:30'
                                                        ],
                                                        className: 'rounded-1 border-0 shadow',
                                                        onShow: function(ct) {
                                                            this.setOptions({
                                                                minDate: $('#Cons-DateEnd').val() ?
                                                                    $(
                                                                        '#Cons-DateEnd').val() :
                                                                    false
                                                            });
                                                        },
                                                        beforeShowDay: function(date) {
                                                            let disabled = false;
                                                            for (let i = 0; i < events.length; i++) {
                                                                let start = new Date(events[i].start);
                                                                let end = new Date(events[i].end);
                                                                if (date.getTime() >= start.getTime() &&
                                                                    date.getTime() <= end.getTime()) {
                                                                    disabled = true;
                                                                    break;
                                                                }
                                                            }
                                                            return [!disabled, ''];
                                                        }
                                                    });
                                                    $('#Cons-DateEnd').on('change', function() {
                                                        if ($('#Cons-DateEnd').val() < $('#Cons-DateStart')
                                                            .val()) {
                                                            $('#Cons-DateEnd').val($('#Cons-DateStart').val());
                                                        }
                                                    });



                                                    $('.txtarea').summernote({
                                                        
                                                    });