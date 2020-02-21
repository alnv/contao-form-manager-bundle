Vue.component( 'number-field', {
    data: function () {
        return {
            value: null,
            range: null,
            active: false,
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
            objCssClass[this.name] = true;
            return objCssClass;
        },
        setRange: function () {
            if ( this.range ) {
                return null;
            }
            this.range = {};
            if ( !this.eval.options ) {
                this.range = {
                    'min': 0,
                    'max': 100
                };
                return  null;
            }
            var current = 1, total = this.getObjectLength();
            for ( var name in this.eval.options ) {
                if ( this.eval.options.hasOwnProperty(name) ) {
                    if ( current === 1 ) {
                        this.range['min'] = parseFloat( this.eval.options[name]['value'] );
                        ++current;
                        continue;
                    }
                    if ( current === total ) {
                        this.range['max'] = parseFloat( this.eval.options[name]['value'] );
                        ++current;
                        continue;
                    }
                    var step = Math.ceil( ( 100 / total ) * current ) + '%';
                    this.range[step] = parseFloat( this.eval.options[name]['value'] );
                    ++current;
                }
            }
        },
        getOptions: function () {
            this.setRange();
            var arrStart = this.value.length ? this.value : [0];
            if (arrStart.length === 1 && this.eval['useBetweenRange']) {
                arrStart.push(this.range['max']);
            }
            return {
                snap: true,
                connect: true,
                start: arrStart,
                range: this.range
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
        getValue: function (type) {
            if ( typeof this.value === 'object' && this.value.length ) {
                if (type === 'min') {
                    return this.value[0];
                }
                if (type === 'max' && this.eval['useBetweenRange']) {
                    return this.value[1];
                }
            }
            return this.range[type];
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
    mounted: function() {
        window.addEventListener('mousedown', function() {
            this.active = false;
        }.bind(this));
        this.$el.addEventListener('mousedown',function(e){
            e.stopPropagation();
        });
    },
    template:
        '<div class="field-component number" v-bind:class="setCssClass()">' +
            '<div class="field-component-container">' +
                '<input type="hidden" v-model="value">'+
                '<div class="range-menu" @click="setActiveMode()">' +
                    '<div class="range-menu-container">' +
                        '<span v-html="eval.label"></span>' +
                    '</div>' +
                '</div>' +
                '<div class="range-input">' +
                    '<div class="range-input-container">' +
                        '<div v-nouislider="getOptions()" @change="onNoUiSliderChange($event)"></div>' +
                        '<div class="range-input-detail">' +
                            '<div class="range-current-value"><span>{{ getValue(\'min\') }}</span></div>' +
                            '<div class="range-reset"><button @click="clearValue()">Alle</button></div>' +
                            '<div class="range-max-value"><span>{{ getValue(\'max\') }}</span></div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
                '<div v-if="eval.description" v-html="eval.description"></div>' +
            '</div>' +
        '</div>'
});
