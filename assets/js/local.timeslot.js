+function ($) {
    "use strict"

    if ($.fn === undefined) $.fn = {}

    if ($.fn.localTimePicker === undefined)
        $.fn.localTimePicker = {}

    var LocalTimePicker = function (element, options) {
        this.$el = $(element)
        this.selectedType = null
        this.options = options

        this.init()
    }

    LocalTimePicker.prototype.constructor = LocalTimePicker

    LocalTimePicker.prototype.dispose = function () {
        this.unregisterHandlers()

        this.$el = null
    }

    LocalTimePicker.prototype.init = function () {
        this.typeSelector = '[data-timepicker-control="type"]'
        this.dateSelector = '[data-timepicker-control="date"]'
        this.timeSelector = '[data-timepicker-control="time"]'

        this.registerHandlers()

        this.fillDates()
        this.togglePicker()
    }

    LocalTimePicker.prototype.registerHandlers = function () {
        this.$el.on('click', '[data-timepicker-option]', $.proxy(this.onOptionClicked, this))
        this.$el.on('change', '[data-timepicker-control]', $.proxy(this.onControlChange, this))
        this.$el.on('click', '[data-timepicker-submit]', $.proxy(this.onSubmitForm, this))
    }

    LocalTimePicker.prototype.unregisterHandlers = function () {
        this.$el.off('click', '[data-timepicker-option]', $.proxy(this.onOptionClicked, this))
        this.$el.off('change', '[data-timepicker-control]', $.proxy(this.onControlChange, this))
        this.$el.off('click', '[data-timepicker-submit]', $.proxy(this.onSubmitForm, this))
    }

    LocalTimePicker.prototype.togglePicker = function () {
        var value = this.selectedType ? this.selectedType : this.$el.find(this.typeSelector).val(),
            $container = this.$el.find('.dropdown-content')

        $('[data-timepicker-option]').removeClass('active')
        $('[data-timepicker-option="'+value+'"]').addClass('active')

        if (value === 'asap') {
            $container.addClass('hide')
        } else {
            $container.removeClass('hide')
        }
    }

    LocalTimePicker.prototype.fillDates = function () {
        var self = this,
            selectedDate = this.$el.find(this.dateSelector).data('timepicker-selected')

        if (!this.options.timeSlot.hasOwnProperty('dates'))
            return

        $.each(this.options.timeSlot.dates, function (index, date) {
            self.$el.find(self.dateSelector).append('<option value="' + index + '"'+ (
                selectedDate === date ? 'selected="selected"' : ''
            ) +'>' + date + '</option>')
        });

        this.$el.find(this.dateSelector).change()
    }

    LocalTimePicker.prototype.fillHours = function () {
        var self = this,
            controlLabel = this.$el.find(this.timeSelector).data('timepicker-label'),
            selectedHour = this.$el.find(this.timeSelector).data('timepicker-selected'),
            selectedDate = this.$el.find(this.dateSelector).val()

        if (!this.options.timeSlot.hours.hasOwnProperty(selectedDate))
            return

        this.$el.find(this.timeSelector).html('<option value="">' + controlLabel + '</option>')
        $.each(this.options.timeSlot.hours[selectedDate], function (index, hour) {
            self.$el.find(self.timeSelector).append('<option value="' + index + '"'+ (
                selectedHour === hour ? 'selected="selected"' : ''
            ) +'>' + hour + '</option>')
        });

        this.$el.find(this.timeSelector).change()
    }

    // EVENT HANDLERS
    // ============================

    LocalTimePicker.prototype.onOptionClicked = function (event) {
        var $button = $(event.currentTarget),
            optionValue = $button.data('timepicker-option')

        event.stopPropagation()

        this.selectedType = optionValue
        this.$el.find(this.typeSelector).trigger('change')

        if (this.selectedType === 'asap')
            this.onSubmitForm()
    }

    LocalTimePicker.prototype.onControlChange = function (event) {
        var $button = $(event.currentTarget),
            picker = $button.data('timepicker-control')

        switch (picker) {
            case 'date':
                this.fillHours($button)
                break
            case 'type':
                this.togglePicker()
                break
        }
    }

    LocalTimePicker.prototype.onSubmitForm = function () {
        var self = this

        self.$el.find(self.typeSelector).val(self.selectedType)

        $('[data-timepicker-submit]').closest('form').request(null, {
            data: {
              asap: this.$el.find(this.typeSelector).val() === 'asap' ? 1 : 0,
              date: this.$el.find(this.dateSelector).val(),
              time: this.$el.find(this.timeSelector).val()
            }
        }).done(function () {
            self.fillDates()
            self.togglePicker()
        })
    }

    LocalTimePicker.DEFAULTS = {
        timeSlot: {},
    }

    var old = $.fn.localTimePicker

    $.fn.localTimePicker = function (option) {
        var args = Array.prototype.slice.call(arguments, 1),
            result = undefined

        this.each(function () {
            var $this = $(this)
            var data = $this.data('ti.localTimePicker')
            var options = $.extend({}, LocalTimePicker.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('ti.localTimePicker', (data = new LocalTimePicker(this, options)))
            if (typeof option == 'string') result = data[option].apply(data, args)
            if (typeof result != 'undefined') return false
        })

        return result ? result : this
    }

    $.fn.localTimePicker.Constructor = LocalTimePicker

    // CART ITEM NO CONFLICT
    // =================

    $.fn.localTimePicker.noConflict = function () {
        $.fn.localTimePicker = old
        return this
    }

    $(document).render(function () {
        $('[data-control="timepicker"]').localTimePicker()
    })
}(window.jQuery)
