Vue.component('flat-pickr', VueFlatpickr);
Vue.component( 'date-field', {
    data: function () {
        return {
            useDatePicker: true,
            config: {
                time_24hr: true,
                noCalendar: this.eval['rgxp'] === 'time',
                enableTime: this.eval['rgxp'] === 'time',
                dateFormat: this.eval['dateFormat'],
                locale: 'de'
            }
        }
    },
    methods: {
        setCssClass: function() {
            let objCssClass = {};
            if ( this.eval['tl_class'] ) {
                objCssClass[this.eval['tl_class']] = true;
            }
            if ( this.eval['mandatory'] ) {
                objCssClass['mandatory'] = true;
            }
            objCssClass[this.name] = true;
            return objCssClass;
        }
    },
    watch: {
        value: function() {
            this.$emit('input', this.value);
        }
    },
    mounted: function() {
        if (this.eval['disableDatePicker'] && this.eval['disableDatePicker'] === true) {
            this.useDatePicker = false;
        }
    },
    props: {
        eval: {
            default: {},
            type: Object,
            required: true
        },
        name: {
            default: '',
            type: Object,
            required: true
        },
        value: {
            type: String,
            default: null,
            required: false
        },
        idPrefix: {
            default: '',
            type: String ,
            required: false
        }
    },
    template:
    '<div class="field-component date" v-bind:class="setCssClass()">' +
        '<div class="field-component-container">' +
            '<label class="label" v-if="eval.label" :for="idPrefix + \'id_\' + name" v-html="eval.label"></label>' +
            '<flat-pickr v-if="useDatePicker" v-model="value" :config="config" class="tl_text" :placeholder="eval.placeholder"></flat-pickr>' +
            '<input v-else type="text" v-model="value" class="tl_text" :id="idPrefix + \'id_\' + name" :placeholder="eval.placeholder" :readonly="eval.readonly">' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<div v-if="eval.description" v-html="eval.description" class="info"></div>' +
        '</div>' +
    '</div>'
});

