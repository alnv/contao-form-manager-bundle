Vue.component('flat-pickr', VueFlatpickr);
Vue.component( 'date-field', {
    data: function () {
        return {
            config: {
                time_24hr: true,
                noCalendar: this.eval['rgxp'] === 'time',
                enableTime: this.eval['rgxp'] !== 'date',
                dateFormat: this.eval['dateFormat']
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
            this.$emit( 'input', this.value );
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
            '<label :for="idPrefix + \'id_\' + name">{{ eval.label }}</label>' +
            '<flat-pickr v-model="value" :config="config" class="tl_text"></flat-pickr>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<div v-if="eval.description" v-html="eval.description"></div>' +
        '</div>' +
    '</div>'
});

