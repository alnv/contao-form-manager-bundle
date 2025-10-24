Vue.component( 'checkbox-field', {
    data: function () {
        return {
            selectAll: false,
            css: {}
        }
    },
    methods: {
        checked: function (strValue) {
            if (Array.isArray(this.value)) {
                return this.value.indexOf(strValue) !== -1;
            }
            return strValue == this.value;
        },
        setSelectAll: function () {
            this.selectAll = !this.selectAll;
            if (this.selectAll && Array.isArray(this.eval.options)) {
                this.value = [];
                for (let i = 0; i < this.eval.options.length; i++) {
                    this.value.push(this.eval.options[i]['value']);
                }
            }
            if (!this.selectAll) {
                this.value = [];
            }
        },
        setCssClass: function() {
            if (this.eval['tl_class']) {
                this.css[this.eval['tl_class']] = true;
            }
            this.css['error'] = this.eval.messages && this.eval.messages.length;
            if ( this.eval['mandatory'] ) {
                this.css['mandatory'] = true;
            }
            this.css['single'] = !this.eval.multiple;
            this.css[this.name] = true;
            return this.css;
        },
        submit: function (value) {
            if (value === this.value) {
                this.$emit('input', this.value, true);
            }
        },
        getSelectAllId: function () {
            return this.idPrefix + '_' + this.name + '_' + 'selectAll';
        },
        reduceOption: function(option) {
            if (typeof option === 'object') {
                return option.value;
            }
            return option;
        }
    },
    created: function() {
        if (this.value === null && this.eval.multiple) {
            this.value = [];
        }
        if (this.value === '1' && this.eval['isSelector']) {
            this.$parent.submitOnChange(this.value, this.name, this.eval['isSelector'])
        }
        if (typeof this.value === 'object' && this.value !== null && typeof this.value.length !== 'undefined') {
            let reduceValues = [];
            for (let i = 0; i < this.value.length; i++) {
                reduceValues[i] = this.reduceOption(this.value[i]);
            }
            this.value = reduceValues;
        }
    },
    watch: {
        value: function() {
            this.$emit('input', this.value);
            if (this.eval.submitOnChange) {
                this.$parent.submitOnChange(this.value, this.name, this.eval['isSelector'])
            }
            this.eval['messages'] = [];

            if (!this.eval.multiple) {
                localStorage.setItem('field-' + this.name + '-' + this.idPrefix, this.value);
            }
        }
    },
    mounted: function () {
        if (this.eval.selectAllLabel) {
            this.selectAllLabel = this.eval.selectAllLabel;
        }
        if (!this.eval.multiple) {
            setTimeout(function () {
                if (!this.value) {
                    this.value = localStorage.getItem('field-' + this.name + '-' + this.idPrefix) ? localStorage.getItem('field-' + this.name + '-' + this.idPrefix) : '';
                }
            }.bind(this), 50);
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
            required: true
        },
        value: {
            default: null,
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
        selectAllLabel: {
            type: String,
            required: false,
            default: 'Alle auswählen'
        }
    },
    template:
    '<div class="field-component checkbox" v-bind:class="setCssClass()">' +
        '<div class="field-component-container">' +
            '<p v-if="eval.multiple && !noLabel" class="label" v-html="eval.label"></p>' +
            '<span v-if="eval.multiple && !eval.disableAllSelection" class="all checkbox-container" v-bind:class="{ \'checked\': selectAll }">' +
                '<input type="checkbox" v-model="selectAll" :id="getSelectAllId()" @click="setSelectAll()">' +
                '<label :for="getSelectAllId()" v-html="selectAllLabel"></label>' +
            '</span>'+
            '<span v-for="(option,index) in eval.options" class="checkbox-container" v-bind:class="{\'checked\': checked(option.value)}">' +
                '<input v-if="eval.multiple" type="checkbox" v-model="value" :value="option.value" :id="idPrefix + \'id_\' + name + \'_\' + index">' +
                '<input v-if="!eval.multiple" type="checkbox" v-model="value" true-value="1" false-value="" :id="idPrefix + \'id_\' + name + \'_\' + index">' +
                '<slot name="label" v-bind:label="option.label" v-bind:id="idPrefix + \'id_\' + name + \'_\' + index"><label :for="idPrefix + \'id_\' + name + \'_\' + index" v-html="option.label"></label></slot>' +
            '</span>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages" v-html="message"></p></template>' +
            '<div v-if="eval.description" v-html="eval.description" class="info"></div>' +
        '</div>' +
    '</div>'
});
