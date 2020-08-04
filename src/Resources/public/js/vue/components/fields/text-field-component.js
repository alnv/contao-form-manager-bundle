Vue.component( 'text-field', {
    data: function () {
        return {
            timeout: null
        }
    },
    watch: {
        value: function() {
            if (this.eval.multiple && this.value.length) {
                for (var i = 0; i < this.eval.size; i++ ) {
                    if (typeof this.value[i] === 'undefined') {
                        this.value[i] = '';
                    }
                }
            }
            if (this.eval['isReactive']) {
                if ( this.timeout !== null ) {
                    clearTimeout(this.timeout);
                }
                this.timeout = setTimeout(function () {
                    this.$emit('input', this.value);
                }.bind(this),800);
            }
            else {
                this.$emit('input', this.value);
            }
        }
    },
    methods: {
        getType: function() {
            if (this.eval.rgxp && this.eval.rgxp === 'natural') {
                return 'number'
            }
            return 'text';
        },
        setCssClass: function() {
            let objCssClass = {};
            if (this.eval['tl_class']) {
                objCssClass[this.eval['tl_class']] = true;
            }
            if (this.eval['class']) {
                objCssClass[this.eval['class']] = true;
            }
            objCssClass['mandatory'] = !!this.eval['mandatory'];
            objCssClass['multiple'] = !!this.eval['multiple'];
            objCssClass[this.name] = true;
            if (this.eval['validate'] === false) {
                objCssClass['error'] = true;
            }
            return objCssClass;
        },
        openModalView: function (e) {
            if ( typeof Backend === 'undefined') {
                return null;
            }
            Backend.openModalSelector({
                "id": "tl_listing",
                "title": this.eval.label,
                "url": e.currentTarget.href + "&value=" + this.value,
                "callback": function(picker, value) {
                    this.value = value.join(",");
                }.bind(this)
            });
        },
        emit: function() {
            this.$emit('input', this.value, true);
        },
        invalid: function () {
            this.eval['validate'] = false;
            this.eval['messages'] = [];
            this.$forceUpdate();
        },

        submit: function () {
            if (!FormHelperValidator.validateMandatory(this.value)) {
                this.invalid();
                return null;
            }
            if (!FormHelperValidator.validateMinLength(this.value, this.eval['minlength'])) {
                this.invalid();
                return false;
            }
            if (!FormHelperValidator.validateMaxLength(this.value, this.eval['maxlength'])) {
                this.invalid();
                return false;
            }
            if (this.eval['loadingView'] && this.eval['loadingViewUrl']) {
                if (this.$parent && typeof this.$parent['setLoadingView'] === 'function') {
                    this.$parent['setLoadingView'](this);
                }
                this.$http.get(this.eval['loadingViewUrl'], {
                    params: {
                        name: this.name,
                        value: this.value,
                        id: this.eval.id ? this.eval.id : ''
                    }
                }).then(function(objResponse) {
                    if (objResponse.body && objResponse.ok) {
                        if (this.$parent && typeof this.$parent['disableLoadingView'] === 'function') {
                            this.$parent['getLoadingViewRequest'](this,objResponse);
                        }
                    }
                }.bind(this));
            } else {
                this.emit();
            }
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
            default: null,
            type: String|Array
        },
        idPrefix: {
            default: '',
            type: String ,
            required: false
        }
    },
    created: function() {
        this.eval.submit = false;
    },
    template:
    '<div class="field-component text" v-bind:class="setCssClass()">' +
        '<div class="field-component-container dcapicker">' +
            '<label class="label" :for="idPrefix + \'id_\' + name" v-html="eval.label"></label>' +
            '<input class="tl_text" class="tl_text" :type="getType()" v-model="value" v-on:keyup.enter.prevent="submit" :id="idPrefix + \'id_\' + name" :placeholder="eval.placeholder" v-if="!eval.multiple">' +
            '<a v-if="eval.dcaPicker" @click.prevent="openModalView($event)" href="/contao/picker?context=link">' +
                '<img src="system/themes/flexible/icons/pickpage.svg" width="16" height="16" alt="">' +
            '</a>'+
            '<div v-if="eval.multiple" class="field-multiple">' +
                '<input v-for="n in eval.size" class="tl_text" type="text" v-model="value[n-1]" :id="idPrefix + \'id_\' + name + (n === 1 ? \'\' : n )" :placeholder="eval.placeholder">' +
            '</div>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<button v-if="eval.showButton" v-html="eval.buttonText" @click.prevent="submit" class="button submit"></button>' +
            '<div v-if="eval.description" v-html="eval.description" class="info"></div>' +
        '</div>' +
    '</div>'
});
