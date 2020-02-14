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
            if ( this.eval['isReactive'] ) {
                if ( this.timeout !== null ) {
                    clearTimeout( this.timeout );
                }
                this.timeout = setTimeout(function () {
                    this.$emit( 'input', this.value );
                }.bind(this),800);
            }
            else {
                this.$emit( 'input', this.value );
            }
        }
    },
    methods: {
        setCssClass: function() {
            let objCssClass = {};
            if ( this.eval['tl_class'] ) {
                objCssClass[this.eval['tl_class']] = true;
            }
            objCssClass['mandatory'] = !!this.eval['mandatory'];
            objCssClass['multiple'] = !!this.eval['multiple'];
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
    template:
    '<div class="field-component text" v-bind:class="setCssClass()">' +
        '<div class="field-component-container dcapicker">' +
            '<label class="label" :for="idPrefix + \'id_\' + name">{{ eval.label }}</label>' +
            '<input class="tl_text" class="tl_text" type="text" v-model="value" :id="idPrefix + \'id_\' + name" :placeholder="eval.placeholder" v-if="!eval.multiple">' +
            '<a v-if="eval.dcaPicker" v-on:click.prevent="openModalView($event)" href="/contao/picker?context=link">' +
                '<img src="system/themes/flexible/icons/pickpage.svg" width="16" height="16" alt="">' +
            '</a>'+
            '<div v-if="eval.multiple" class="field-multiple">' +
                '<input v-for="n in eval.size" class="tl_text" type="text" v-model="value[n-1]" :id="idPrefix + \'id_\' + name + (n === 1 ? \'\' : n )" :placeholder="eval.placeholder">' +
            '</div>' +
            '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
            '<template v-if="eval.description"><p class="description">{{ eval.description }}</p></template>' +
        '</div>' +
    '</div>'
});
