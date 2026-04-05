$(document).ready(function() {
    // Initialize form validation
    $('#employeeRegistrationModal form').validate({
        rules: {
            employee_id: {
                required: true,
                minlength: 2,
                maxlength: 50
            },
            first_name: {
                required: true,
                minlength: 2,
                maxlength: 50
            },
            father_name: {
                required: true,
                minlength: 2,
                maxlength: 50
            },
            g_father_name: {
                required: true,
                minlength: 2,
                maxlength: 50
            },
            mother_name: {
                required: true,
                minlength: 2,
                maxlength: 100
            },
            sex: {
                required: true
            },
            birth_date: {
                required: true,
                date: true,
                ageRange: [18, 65]
            },
            phone_number: {
                required: true,
                pattern: /^[0-9]{10}$/
            },
            yegabcha_huneta: {
                required: true
            },
            job_property_id: {
                required: true
            },
            date_of_employed: {
                date: true
            },
            level_of_education: {
                required: true
            },
            department: {
                maxlength: 100
            },
            employment_situation: {
                required: true
            },
            immidate_boss: {
                maxlength: 100
            },
            annual_rest: {
                number: true,
                min: 0,
                max: 365
            },
            effeciency: {
                number: true,
                min: 0,
                max: 100
            },
            no_of_files_in_folder: {
                number: true,
                min: 0
            },
            experience: {
                maxlength: 200
            },
            displin_situation: {
                required: true
            },
            competency_situation: {
                required: true
            },
            employee_image: {
                required: true,
                extension: "jpg|jpeg|png|gif",
                filesize: 5242880 // 5MB
            },
            employee_file201: {
                required: true,
                extension: "pdf|doc|docx|jpg|jpeg|png",
                filesize: 10485760 // 10MB
            },
            remark: {
                maxlength: 500
            }
        },

        messages: {
            employee_id: {
                required: "የሰራተኛ መለያ ቁጥር አስፈላጊ ነው።",
                minlength: "የሰራተኛ መለያ ቁጥር ቢያንስ 2 ዲጅት መሆን አለበት።",
                maxlength: "የሰራተኛ መለያ ቁጥር ከ50 ፊደል መብለጥ የለበትም።"
            },
            first_name: {
                required: "ስም አስፈላጊ ነው።",
                minlength: "ስም ቢያንስ 2  ፊደል መሆን አለበት።",
                maxlength: "ስም  ከ50 ፊደል መብለጥ የለበትም።"
            },
            father_name: {
                required: "የአባት ስም አስፈላጊ ነው።",
                minlength: "የአባት ስም ቢያንስ 2  ፊደል መሆን አለበት።",
                maxlength: "የአባት ስም  ከ50 ፊደል መብለጥ የለበትም።"
            },
            g_father_name: {
                required: "የአያት ስም አስፈላጊ ነው።",
                minlength: "የአያት ስም ቢያንስ 2  ፊደል መሆን አለበት።",
                maxlength: "የአያት ስም  ከ50 ፊደል መብለጥ የለበትም።"
            },
            mother_name: {
                required: "የእናት ሙሉ ስም አስፈላጊ ነው።",
                minlength: "የእናት ስም ቢያንስ 2  ፊደል መሆን አለበት።",
                maxlength: "የእናት ስም 100  ፊደል ከመብለጫ ቀር መሆን አለበት።"
            },
            sex: {
                required: "ጾታ መምረጥ አስፈላጊ ነው።"
            },
            birth_date: {
                required: "የትውልድ ቀን አስፈላጊ ነው።",
                date: "ትክክለኛ ቀን ያስገቡ።",
                ageRange: "ዕድሜ ከ 18 እስከ 65 አመት መሆን አለበት።"
            },
            phone_number: {
                required: "ስልክ ቁጥር አስፈላጊ ነው።",
                pattern: "ስልክ ቁጥር ትክክለኛ 10 አሃዝ መሆን አለበት።"
            },
            yegabcha_huneta: {
                required: "የጋብቻ ሁኔታ አስፈላጊ ነው።",
                minlength: "የጋብቻ ሁኔታ ቢያንስ 2  ፊደል መሆን አለበት።",
                maxlength: "የጋብቻ ሁኔታ  ከ50 ፊደል መብለጥ የለበትም።"
            },
            job_property_id: {
                required: "የስራ መደብ መምረጥ አስፈላጊ ነው።"
            },
            level_of_education: {
                required: "የትምህርት ደረጃ አስፈላጊ ነው።",
                minlength: "የትምህርት ደረጃ ቢያንስ 2  ፊደል መሆን አለበት።",
                maxlength: "የትምህርት ደረጃ 100  ፊደል ከመብለጫ ቀር መሆን አለበት።"
            },
            employment_situation: {
                required: "Employment Situation አስፈላጊ ነው።",
                minlength: "Employment Situation ቢያንስ 2  ፊደል መሆን አለበት።",
                maxlength: "Employment Situation 100  ፊደል ከመብለጫ ቀር መሆን አለበት።"
            },
            displin_situation: {
                required: "የዲሲፕሊን ሁኔታ አስፈላጊ ነው።",
                minlength: "የዲሲፕሊን ሁኔታ ቢያንስ 2  ፊደል መሆን አለበት።",
                maxlength: "የዲሲፕሊን ሁኔታ 100  ፊደል ከመብለጫ ቀር መሆን አለበት።"
            },
            employee_image: {
                required: "ፎቶ አስፈላጊ ነው።",
                extension: "ፎቶ ፋይል መሆን አለበት (jpg, jpeg, png, gif)።",
                filesize: "ፎቶ ፋይል 5MB ከመብለጫ ቀር መሆን አለበት።"
            },
            employee_file201: {
                required: "የት/ት ማስረጃ እና ሌሎች ፋይል አስፈላጊ ነው።",
                extension: "ትክክለኛ ፋይል አይነት ያስገቡ (pdf, doc, docx, jpg, jpeg, png)።",
                filesize: "ፋይል 10MB ከመብለጫ ቀር መሆን አለበት።"
            }
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        },
        submitHandler: function(form) {
            // Show loading state
            const submitBtn = $(form).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> በማስቀመጥ ላይ...');

            // Submit form
            form.submit();
        }
    });

    // Custom validation method for file size
    $.validator.addMethod('filesize', function(value, element, param) {
        return this.optional(element) || (element.files[0].size <= param);
    }, 'ፋይል ከፍተኛ ነው።');

    // Custom validation method for age range
    $.validator.addMethod('ageRange', function(value, element, param) {
        if (this.optional(element)) return true;
        
        var birthDate = new Date(value);
        var today = new Date();
        var age = today.getFullYear() - birthDate.getFullYear();
        var monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        return age >= param[0] && age <= param[1];
    }, 'ዕድሜ ከ 18 እስከ 65 አመት መሆን አለበት።');

    // Calculate efficiency level based on efficiency percentage
    $('#effeciency').on('input', function() {
        const value = parseFloat($(this).val());
        let level = '';
        if (value >= 90) {
            level = 'ከፍተኛ';
        } else if (value >= 70) {
            level = 'መካከለኛ';
        } else if (value >= 50) {
            level = 'ያልተለመደ';
        } else {
            level = 'ያልተለመደ';
        }
        $('#level_of_effeciency').val(level);
    });

    // Make modal draggable
    $('#employeeRegistrationModal').on('shown.bs.modal', function() {
        // Make the modal draggable by its header
        $(this).find('.modal-dialog').draggable({
            handle: '.modal-header',
            containment: 'window',
            scroll: false
        });

        // Add cursor style to indicate draggable
        $(this).find('.modal-header').css('cursor', 'move');
    });

    // Reset form when modal is closed
    $('#employeeRegistrationModal').on('hidden.bs.modal', function() {
        $('#employeeRegistrationModal form')[0].reset();
        $('#employeeRegistrationModal form').validate().resetForm();
        $('#employeeRegistrationModal .form-control').removeClass('is-invalid');
        $('#employeeRegistrationModal .invalid-feedback').remove();

        // Reset modal position
        $(this).find('.modal-dialog').removeAttr('style');
    });
});