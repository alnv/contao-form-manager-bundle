Vue.component( 'select-field', {
    data: function () {
        return {}
    },
    methods: {
        setCssClass: function() {
            let objCssClass = {};
            if (this.eval['tl_class']) {
                objCssClass[this.eval['tl_class']] = true;
            }
            if (this.eval['class']) {
                objCssClass[this.eval['class']] = true;
            }
            if ( this.eval['mandatory'] ) {
                objCssClass['mandatory'] = true;
            }
            objCssClass[this.name] = true;
            return objCssClass;
        },
        reduceOption: function(option) {
            if (typeof option === 'object') {
                return option.value;
            }
            return option;
        },
        hasModal: function() {
            return typeof this.eval.modal !== 'undefined';
        },
        openModal: function() {
            this.eval.modal.component.field = {
                name: this.name,
                value: this.value
            };
            window.VueData.modal = this.eval.modal;
        }
    },
    watch: {
        value: function() {
            this.$emit('input', this.value);
            if (this.eval.submitOnChange) {
                this.$parent.submitOnChange(this.value, this.name, this.eval['isSelector'])
            }
        }
    },
    created: function() {
        if (this.value === '' || this.value === null) {
            return null;
        }
        if (typeof this.value === 'object' && typeof this.value.length !== 'undefined') {
            let reduceValues = [];
            for (let i = 0; i < this.value.length; i++) {
                reduceValues[i] = this.reduceOption(this.value[i]);
            }
            this.value = reduceValues;
        }
    },
    mounted: function() {
        if (this.eval.useNativeSelect) {
            this.useNativeSelect = this.eval.useNativeSelect;
        }
    },
    props: {
        eval: {
            default: {},
            type: Object,
            required: true
        },
        name: {
            type: String,
            default: null,
            required: true
        },
        value: {
            default: null,
            required: false,
            type: String|Array
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
        },
        useNativeSelect: {
            type: Boolean,
            default: false,
            required: false
        }
    },
    template:
    '<div class="field-component select" v-bind:class="setCssClass()">' +
        '<div class="field-component-container">' +
            '<label v-if="eval.label && !noLabel" class="label" :for="idPrefix + \'id_\' + name" v-html="eval.label"></label>' +
            '<div class="select-container">' +
                '<v-select v-if="!useNativeSelect" v-model="value" :id="idPrefix + \'id_\' + name" :clearable="false" :placeholder="eval.blankOptionLabel" :multiple="eval.multiple" :options="eval.options" label="label" :reduce="reduceOption">' +
                    '<template v-slot:selected-option="option">' +
                        '<slot name="label" v-bind:label="option.label" v-html="option.label"></slot>' +
                    '</template>' +
                    '<template v-slot:option="option">' +
                        '<slot name="label" v-bind:label="option.label" v-html="option.label"></slot>' +
                    '</template>' +
                '</v-select>' +
                '<select v-else v-model="value" :id="idPrefix + \'id_\' + name" :placeholder="eval.label" :multiple="eval.multiple">' +
                    '<option v-for="option in eval.options" v-bind:value="reduceOption(option)" v-html="option.label"></option>' +
                '</select>' +
                '<button v-if="hasModal()" class="button modal" v-html="eval.modal.buttonText" @click.prevent="openModal"></button>' +
            '</div>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<div v-if="eval.description" v-html="eval.description" class="info"></div>' +
        '</div>' +
    '</div>'
});

