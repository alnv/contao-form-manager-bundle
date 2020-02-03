Vue.component( 'number-field', {
    data: function () {
        return {
            timeout: null
        }
    },
    watch: {
        value: function() {
            this.$emit( 'input', this.value );
            this.$parent.submitOnChange( this.value, this.name, false )
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
                start: [0],
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
        onChange: function ($event) {
            this.value = $event.target.value;
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
                '<p>{{ eval.label }}</p>' +
                '<div v-nouislider="getOptions()" @change="onChange($event)"></div>' +
                '<template v-if="!eval.validate"><p class="error" v-for="message in eval.messages">{{ message }}</p></template>' +
                '<template v-if="eval.description"><p class="description">{{ eval.description }}</p></template>' +
            '</div>' +
        '</div>'
});
