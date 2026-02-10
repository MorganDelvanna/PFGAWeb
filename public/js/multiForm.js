var applicationTypeChange = function () {
    let selectedValue = $('input[name="applicationType"]:checked').val();
    switch (selectedValue) {
        case "new":
            if (!$('label.new').first().hasClass('required')) {
                $('label.new').addClass('required');
                $('input.new').attr('required');
            }
            $('#cardCell').hide();
            $('#cardLabel').removeClass('required');
            $('#card').removeAttr('required');
            $('.newOnly').show();
            if ($('#archeryBtn').is(':checked')) {
                $('#generalBtn').prop("checked", true);
                $('[name="membershipFee"]').trigger('change');
            }
            $('#archeryBtn').hide();
            $('#initiationFee').text(`$${initiation}`);
            break;
        case "half":
            if (!$('label.new').first().hasClass('required')) {
                $('label.new').addClass('required');
                $('input.new').attr('required');
            }
            $('#cardLabel').removeClass('required');
            $('#card').removeAttr('required');
            $('.newOnly').show();
            $('#cardCell').hide();
            $('#archeryBtn').show();
            $('#initiationFee').text(`$${initiation}`);
            break;
        case "renew":
            $('label.new').removeClass('required');
            $('input.new').removeAttr('required');
            $('#cardCell').show();
            $('#cardLabel').addClass('required');
            $('#card').attr('required');
            $('.newOnly').hide();
            $('#initiationFee').text(`$0`);
            $('#archeryBtn').show();
            break;
    }
    //recalc();
}

$(function () {
    var $inputs = $('input[name=homephone],input[name=cellphone]');
    $inputs.on('input', function () {
        // Set the required property of the other input to false if this input is not empty.
        $inputs.not(this).prop('required', !$(this).val().length);
    });

    $('[name="applicationType"]').on("change", applicationTypeChange);

    $('[name="palType"]').on('change', function () {
        let selectedValue = $('[name="palType"]:checked').val();
        let PALNum = $('#PALNum');
        let palExpiry = $('#palExpiry');

        switch (selectedValue) {
            case "pal":
            case "rpal":
                if (!PALNum.hasClass('required')) {
                    PALNum.attr('required');
                    palExpiry.attr('required');
                    $('label.pal').addClass('required');
                }
                break;
            case "noPal":
                PALNum.removeAttr('required');
                palExpiry.removeAttr('required');
                $('label.pal').removeClass('required');
        }
    });

    $('#addFamily').on('click', function () {
        let template = $('#familyTemplate').html();
        $('.familyRow:last').after(template);
        $('.btnDeleteFam').on('click', function () {
            $(this).closest(".familyRow").remove();
            let famNum = parseInt($('#family').val());
            if (famNum > 0) {
                $('#family').val(famNum - 1);
            }
        });
        let famNum = parseInt($('#family').val());
        $('#family').val(famNum + 1);
        recalc();
    });

    $('#addClub').on('click', function () {
        let template = $('#clubTemplate').html();
        $('.clubRow').after(template);
        $('.btnDeleteClub').on('click', function () {
            $(this).closest(".clubRow").remove();
        });
        $('.otherFrom').inputmask("99/99");
        $('.otherTo').inputmask("99/99");
    });

    $('#addCourse').on("click", function () {
        let template = $('#courseTemplate').html();
        $('.courseRow:last').after(template);
        $('.btnDeleteCourse').on("click", function () {
            $(this).closest(".courseRow").remove();
        });
        $('.courseDate').inputmask("99/99");
    });
});