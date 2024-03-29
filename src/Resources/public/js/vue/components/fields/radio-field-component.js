Vue.component('radio-field', {
    data: function () {
        return {
            css: {}
        }
    },
    methods: {
        checked: function ( strValue ) {
            if (Array.isArray(this.value)) {
                return this.value.indexOf(strValue) !== -1;
            }
            return this.value == strValue;
        },
        setDefault: function () {
            if (this.eval.options) {
                for (let i=0; i < this.eval.options.length; i++) {
                    if (this.eval.options[i]['default']) {
                        this.value = this.eval.options[i]['value'];
                    }
                }
            }
        },
        setCssClass: function() {
            if (this.eval['tl_class']) {
                this.css[this.eval['tl_class']] = true;
            }
            if ( this.eval['mandatory'] ) {
                this.css['mandatory'] = true;
            }
            this.css['error'] = this.eval['validate'] === false;
            this.css[this.name] = true;
            return this.css;
        },
        submit: function (value) {
            setTimeout(function () {
                this.$emit('input', value, true);
            }.bind(this),100);
        },
        emit: function () {
            this.$emit('input', this.value, true);
        }
    },
    watch: {
        value: function() {
            this.$emit('input', this.value);
            if (this.eval.submitOnChange) {
                this.$parent.submitOnChange(this.value, this.name)
            }
            this.eval['validate'] = true;
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
            default: null
        },
        idPrefix: {
            default: '',
            type: String ,
            required: false
        },
        noLabel: {
            type: Boolean,
            default: false,
            required: false
        }
    },
    mounted: function () {
        this.setDefault();
    },
    template:
    '<div class="field-component radio" v-bind:class="setCssClass()">' +
        '<div class="field-component-container">' +
            '<p v-if="eval.label && !noLabel" class="label" v-html="eval.label"></p>' +
            '<span v-for="(option, index) in eval.options" class="radio-container" v-bind:class="{ \'checked\': checked(option.value) }">' +
                '<input type="radio" v-model="value" :value="option.value" :id="idPrefix + \'id_\' + name + \'_\' + index" @click="submit(option.value)">' +
                '<slot name="label" v-bind:label="option.label" v-bind:id="idPrefix + \'id_\' + name + \'_\' + index"><label :for="idPrefix + \'id_\' + name + \'_\' + index" v-html="option.label"></label></slot>' +
            '</span>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<div v-if="eval.description" v-html="eval.description" class="info"></div>' +
        '</div>' +
    '</div>'
});
