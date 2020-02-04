Vue.component( 'number-field', {
    data: function () {
        return {
            value: null,
            active: false
        }
    },
    watch: {
        value: function() {
            this.$emit( 'input', this.value );
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
            objCssClass['active'] = this.active;
            return objCssClass;
        },
        getRange: function () {
            if ( !this.eval.options ) {
                return {
                    'min': 0,
                    'max': 100
                };
            }
            var current = 1, objOptions = {}, total = this.getObjectLength();
            for ( var name in this.eval.options ) {
                if ( this.eval.options.hasOwnProperty(name) ) {
                    if ( current === 1 ) {
                        objOptions['min'] = parseFloat( this.eval.options[name]['value'] );
                        ++current;
                        continue;
                    }
                    if ( current === total ) {
                        objOptions['max'] = parseFloat( this.eval.options[name]['value'] );
                        ++current;
                        continue;
                    }
                    var step = Math.ceil( ( 100 / total ) * current ) + '%';
                    objOptions[step] = parseFloat( this.eval.options[name]['value'] );
                    ++current;
                }
            }
            return objOptions;
        },
        getOptions: function () {
            return {
                start: this.value.length ? this.value : [0],
                snap: true,
                connect: true,
                range: this.getRange()
            }
        },
        getObjectLength: function () {
            var length = 0;
            for( var name in this.eval.options ) {
                if( this.eval.options.hasOwnProperty(name) ) {
                    ++length;
                }
            }
            return length
        },
        onNoUiSliderChange: function ($event) {
            this.value = $event.target.value;
        },
        getValue: function () {
            return typeof this.value === 'object' ? this.value.join('') : this.value;
        },
        clearValue: function () {
            this.value = [];
        },
        setActiveMode: function () {
            for ( var i = 0; i < this.$parent.$children.length; i++ ) {
                if ( this.$parent.$children[i].$vnode !== this.$vnode && this.$parent.$children[i].$vnode.componentOptions.tag === 'number-field') {
                    this.$parent.$children[i].active = false;
                }
            }
            this.active = !this.active;
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
        '<div class="field-component number" v-bind:class="setCssClass()">' +
            '<div class="field-component-container">' +
                '<input type="hidden" v-model="value">'+
                '<div class="range-menu" @click="setActiveMode()">' +
                    '<div class="range-menu-container">' +
                        '<span>{{ eval.label }}</span>' +
                    '</div>' +
                '</div>' +
                '<div class="range-input">' +
                    '<div class="range-input-container">' +
                        '<div v-nouislider="getOptions()" @change="onNoUiSliderChange($event)"></div>' +
                        '<div class="range-input-detail">' +
                            '<div class="range-current-value"><span>{{ getValue() ? getValue() : getOptions()[\'range\'][\'min\'] }}</span></div>' +
                            '<div class="range-reset"><button @click="clearValue()">Alle</button></div>' +
                            '<div class="range-max-value"><span>{{ getOptions()[\'range\'][\'max\'] }}</span></div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
                '<template v-if="eval.description"><p class="description">{{ eval.description }}</p></template>' +
            '</div>' +
        '</div>'
});
