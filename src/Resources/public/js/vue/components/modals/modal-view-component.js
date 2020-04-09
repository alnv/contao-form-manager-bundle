const modalViewComponent = Vue.component( 'modal-view', {
    data: function () {
        return window.VueData;
    },
    methods: {
        close: function () {
            this.$parent.onChange(this);
            this.modal = null;
        },
        submitSingleForm: function (form,response) {
            window.VueData._modal = {
                created: response.id,
                field: this.modal.component.field.name
            };
            this.$parent.onChange(this);
            this.close();
        }
    },
    props: {},
    template:
        '<div v-if="modal" class="modal-component">' +
            '<div class="modal-container">' +
                '<button @click="close">Close</button>' +
                '<component v-if="modal.component.name === \'single-form\'" :model="{type:modal.component.field.name}" :submit-callback="submitSingleForm" :is="modal.component.name" :id="modal.component.params.id" :identifier="modal.component.params.identifier" :source="modal.component.params.source"></component>' +
            '</div>' +
        '</div>'
});