(function($){
    $(document).ready(function()
    {
        /*
         * Includes stylesheet for scripting purposes only
         * Use document.createStyleSheet for IE workaround. The CSS is not loaded in IE8
         */
        if (document.createStyleSheet) {
            document.createStyleSheet("/wp-content/plugins/rhswp-releasekalender/public/css/script-styles.css");
        } else {
            $('head').append('<link rel="stylesheet" href="/wp-content/plugins/rhswp-releasekalender/public/css/script-styles.css" type="text/css" media="screen" />');
        }

        $('body').addClass('js-active');

        // Save the sate of the mouse button globbaly for use in the :focus event of the timeline,
        // so that we can see if a release is focused with mouse or 'Tab' key
        var mousedown;
        $(document).mousedown(function() { mousedown = true; });
        $(document).mouseup(function() { mousedown = false; });
        var tabOffsetCorrection = 0;

        /*
         * Visualiseer projecten en te realiseren resultaten op tijdbalk
         */
        if($('.tijdbalk').length > 0)
        {
            $('.tijdbalk').show();
            $('.tijdbalk li').attr('unselectable', 'on'); // IE fix

            // breedte van een dag (inclusief witruimte) in px
            var jaar_1 = $('.tijdbalk>ul>li').eq(0).offset();
            var jaar_2 = $('.tijdbalk>ul>li').eq(1).offset();
            var day_px = (3 + jaar_2.left - jaar_1.left) / 365.25;

            // big bang: begin van de tijd(balk)
            var big_bang = $('.tijdbalk>ul>li').eq(0).text().split(' ');
            big_bang = '1 januari ' + $.trim(big_bang[0]);
            big_bang = $.datepicker.parseDate('d MM yy', big_bang);

            // Standlijn
            function getStandlijnLabelTop() {
                return $('.tijdbalk').position().top - 25;
            }

            $('.nu').each(function()
            {
                // Datum label onder bouwsteenbeschrijving
                // During initialization getStandlijnLabelTop return a different value. Removed the margin and border of
                // the container to position it correctly.
                $(this).children('p').css('top', getStandlijnLabelTop() + 'px');

                // Positioneer standlijn
                var nu = $.datepicker.parseDate('mm/dd/yy', $(this).find('p').data( "datumnu") );
                var left = daydiff(big_bang, nu) * day_px + 5;
                $(this).css('left', left);

                // Center scherm op standlijn

                var centerWidth = Math.floor($('#releasekalenderoutput').width() / 2);
                var diff = (left - centerWidth) * -1;
                $('.tijdbalk>ul').css('left', diff);
                $('.programma li li, .nu').css('margin-left', diff);
                $('.nu').fadeIn();
            });

            // verwerk datum per resultaat
            $('.programma li li').each(function()
            {
                $(this).find('span.datum').each(function()
                {
                    var data = $.datepicker.parseDate('d MM yy', $(this).text());
                    var left = daydiff(big_bang, data) * day_px;
                    $(this).parent().parent().css('left', left);
                });
            });

            // Verdeel releases die over elkaar heen liggen over meerdere regels
            var rowHeight = 38,
                rowCount;
            $('.programma > ul > li').each(function() {
                rowCount = 1;
                $(this).find('li').each(function() {
                    if ($(this).next().length > 0 && $(this).next().position().left - $(this).position().left< $(this).outerWidth()) {
                        $(this).next().css('margin-top', rowHeight*rowCount + 'px');
                        rowCount++;
                    }
                });
                $(this).css('height', 9 + rowHeight*rowCount + 'px');
            })

            // Hoogte standlijn. Heeft tijd nodig om CSS bij te laten werken
            function getStandlijnHeight() {
                return $('.programma').height() + $('.programma').position().top;
            }
            setTimeout(function() {
                $('.nu').height(getStandlijnHeight());
            }, 500);

            // Sticky tijdsbalk
            var tijdbalkOffset = $('.tijdbalk').offset();
            var tijdbalkMarginLeft = $('.tijdbalk>ul').css('margin-left').replace('px', '');
            $(window).scroll(function()
            {
                var top = (document.documentElement && document.documentElement.scrollTop) || document.body.scrollTop;
                if(top > tijdbalkOffset.top) {
                    // als naar tijdsbalk uit beeld wordt gescrolld wordt 'ie sticky
                    $('.tijdbalk').addClass('sticky');
                    $('.tijdbalk').css('margin-left', $('#releasekalenderoutput').offset().left);
                    $('.tijdbalk').css('width', $('#releasekalenderoutput').width());
                    $('.nu').height(getStandlijnHeight());
                    $('.nu p').css('position', 'fixed').css('top', '42px');
                }
                else {
                    // als tijdsbalk in beeld wordt gescrolld is 'ie niet meer sticky
                    $('.tijdbalk').removeClass('sticky');
                    $('.tijdbalk').css('margin-left', '0');
                    $('.tijdbalk').css('width', $('#releasekalenderoutput').width());
                    $('.tijdbalk>ul').css('margin-left', parseFloat(tijdbalkMarginLeft));
                    $('.nu').height(getStandlijnHeight());
                    $('.nu p').css('position', 'absolute').css('top', getStandlijnLabelTop());
                }
            });

            // Tijdbalk herpositioneren tov de pagina
            $(window).resize(function() {
                if($('.tijdbalk').hasClass('sticky')) {
                    $('.tijdbalk').css('margin-left', $('#releasekalenderoutput').offset().left);
                    $('.tijdbalk').css('width', $('#releasekalenderoutput').width());
                } else {
                    $('.tijdbalk').css('margin-left', '0');
                    $('.tijdbalk').css('width', $('#releasekalenderoutput').width());
                }
            });

            // Drag tijdsbalk
            var tijdbalk = $('.tijdbalk').offset(),
                // li width * number of li's - borderwidth
                totalWidth = $('.tijdbalk > ul').children('li').last().outerWidth(true)*$('.tijdbalk > ul').children('li').length-4;

            var x1 = tijdbalk.left - totalWidth + ($('.tijdbalk').width()); //*.8; // rechtergrens, (evt. 1/5 extra)
            var x2 = tijdbalk.left; // + $('.tijdbalk').width()*.2; // linkergrens, (evt. 1/5 extra)
            $('.tijdbalk>ul').draggable({
                axis: "x",
                distance: 15,
                containment: [ x1, 0, x2, 0 ],
                drag: function(event, ui)
                {
                    // drag ook (resp.): project, resultaat, standlijn
                    $('.programma li li, .nu').css('margin-left', $(this).css('left'));
                }
            });
        }

        /**
         * Tijdbalk control
         */
        $('.tijdbalk').prepend('<a href="#eerder" class="eerder">Eerder</a>');
        $('.tijdbalk').append('<a href="#later" class="later">Later</a>');
        $('.tijdbalk>a').click(function(e)
        {
            var draggable = $('.tijdbalk>ul');
            var position = draggable.position();
            var width = draggable.children('li').length*draggable.children('li').last().outerWidth(true)-4;
            var distance = 150;

            // Calculate the new position, including boundaries
            var newPosition = $(this).hasClass('eerder')?position.left + distance:position.left - distance,
                min = -width+$('.tijdbalk').outerWidth(),
                max = 0;
            newPosition = Math.min(max, Math.max(min, newPosition));

            // render all
            draggable.animate({
                left: newPosition
            }, 150);
           // draggable.css(position);
            $('.programma li li, .nu').animate({
                marginLeft: newPosition
            }, 150);

            // IE8 does not support event.preventDefault()
            (e.preventDefault) ? e.preventDefault() : e.returnValue = false;
        });

        /* Add tab control */
        $('.programma li li a').focus(function(e) {
            if (!mousedown) {
                // In the css is specified that on li:hover/focus the z-index of the li will be 999.
                // When tabbing, the li is not focused, but the a, so we set them with javascript
                $(this).parent().css('z-index', 999);

                var newPosition = -$(this).parent().position().left+0.5*$('.tijdbalk').width();
                $('.tijdbalk>ul').animate({
                    left: newPosition
                }, 300);
                $('.programma li li, .nu').animate({
                   marginLeft: newPosition
                }, 300);
            }
        })
        $('.programma li li a').focusout(function(e) {
            $(this).parent().css('z-index', "");
        });

        /*
         * Add cursor control
         */
        $('body').keydown(function(e)
        {
            switch (e.keyCode)
            {
                // links
                case 37:
                    $('.tijdbalk .eerder, .kalender .prev').click();
                    break;
                // rechts
                case 39:
                    $('.tijdbalk .later, .kalender .next').click();
                    break;
            }
        });

        // Kalender controls
        if ($('.kalender').length > 0) {
            function nav(e) {
                (e.preventDefault) ? e.preventDefault() : e.returnValue = false;
                if (!$(this).hasClass('disabled')) {
                    var direction = $(this).hasClass('prev')?'+':'-';
                    $('.unitcontainer').stop(true, true).animate({'margin-left' : direction + "=" + unitWidth}, toggleControls);
                }
            }

            function toggleControls() {
                var left = parseInt($('.kalender .unitcontainer').css('margin-left')) - tabOffsetCorrection;
                if (left >= 0) {
                    $('.kalender .prev').addClass('disabled');
                } else {
                    $('.kalender .prev').removeClass('disabled');
                }
                if (left <= -($('.kalender .unit').length-nrOfUnitVisable)*unitWidth) {
                    $('.kalender .next').addClass('disabled');
                } else {
                    $('.kalender .next').removeClass('disabled');
                }
            }

            function moveKalender(position, animate) {
                // We have to cope with the tab key. The tab key will move the container to
                // show the selected (anchor) element
                // We have to correct this additional browser offset..
                var curMarginLeft = parseInt($('.kalender .unitcontainer').css('margin-left'), 10);
                curMarginLeft = isNaN(curMarginLeft) ? 0 : curMarginLeft;
                tabOffsetCorrection = curMarginLeft - ($('.kalender .unitcontainer').offset().left - $('.months').offset().left);
                tabOffsetCorrection = Math.ceil(tabOffsetCorrection);
                if (tabOffsetCorrection > 0) {
                    $('.unit h3').removeClass('sticky');
                }

                animate = animate || false;
                if (animate) {
                    $('.kalender .unitcontainer').stop(true, true).animate({'margin-left' : -position + unitWidth + tabOffsetCorrection}, toggleControls);
                } else {
                    $('.kalender .unitcontainer').css('margin-left', -position + unitWidth + tabOffsetCorrection + 'px');
                    toggleControls();
                }
            }
            // Determine the width of the unit to use, baased on the #releasekalenderoutput width.
            var unitWidth = '60%';
            var monthWidth = '70%';
            var nrOfUnitVisable = 1;
            if ($('#releasekalenderoutput').width() > 600) {
                unitWidth = '19%';
                monthWidth = '86%';
                nrOfUnitVisable = 4;
            } else if ($('#releasekalenderoutput').width() > 380) {
                unitWidth = '40%';
                monthWidth = '70%';
                nrOfUnitVisable = 2;
            }

            $('.kalender .unit').css('width', unitWidth);
            $('.kalender').prepend('<a href="#prev" class="prev">Vorige maand</a>');
            $('.kalender').append('<a href="#next" class="next">Volgende maand</a>');
            // We don't want tabbing to select the years as well, remove them instead.
            $('.rk-kalender h2, .rk-kalender .back_to_top, #skipjaar').remove();

            // Set width hard in div
            var unit = $('.kalender .unit').first();
            var width = unit.width();
            var paddingLeft = unit.css('padding-left');
            var paddingRight = unit.css('padding-right');
            var paddingTop = unit.css('padding-top');
            var paddingBottom = unit.css('padding-bottom');
            $('.kalender .unit').each(function() {
                $(this).css('width', width);
                $(this).css('padding-left', paddingLeft);
                $(this).css('padding-right', paddingRight);
                $(this).css('padding-top', paddingTop);
                $(this).css('padding-bottom', paddingBottom);
            });

            // Restructure.
            $('.kalender .months').css('width', monthWidth);
            $('.kalender .row').wrapAll('<div class="unitcontainer" />');
            $('.kalender .unit').unwrap();
            $('.kalender .months').height($('.kalender .unitcontainer').height());

            // Initialize position to current month
            var currentDate = $.datepicker.formatDate('MM yy', new Date())
            var currentMonth = $('.kalender .unit h3:contains(' + currentDate + ')').parent();
            if(!currentMonth.length) {
              var currentMonth = $('.kalender .unit').first();
            }

            var unitWidth = currentMonth.outerWidth()-1;
            moveKalender(currentMonth.position().left);
            toggleControls();

            $('.kalender .next, .kalender .prev').click(nav);
            $('.kalender .unit li a').focus(function(e) {
                if (!mousedown) {
                    moveKalender($(this).closest('.unit').position().left, true);
                }
            })

            var datumOffset = $('.kalender .unit').offset();
            $(window).scroll(function () {
                var top = (document.documentElement && document.documentElement.scrollTop) || document.body.scrollTop;
                if (top > datumOffset.top && tabOffsetCorrection === 0) {
                    $('.unit h3').addClass('sticky');
                }
                else {
                    $('.unit h3').removeClass('sticky');
                }
            });
        }

    });
    /**
     * Bereken verschil tussen twee data in hele dagen
     * @param {Date} first
     * @param {Date} second
     */
    function daydiff(first, second)
    {
        return (second-first)/(1000*60*60*24);
    }
})(jQuery);