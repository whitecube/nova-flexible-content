<template>
    <PanelItem :field="field">
        <template #value>
            <div v-for="(group, index) in groups">
                <detail-nova-flexible-content-group
                    :index="index"
                    :last="(index === groups.length - 1)"
                    :group="group"
                    :resource="resource"
                    :resourceName="resourceName"
                    :resourceId="resourceId"
                    :attribute="field.attribute"
                />
            </div>
        </template>
    </PanelItem>
</template>

<script>
import Group from '../group';

export default {

    props: ['resource', 'resourceName', 'resourceId', 'field'],

    computed: {
        groups() {
            let group;
            return this.field.value.reduce((groups, item) => {
                if(!(group = this.getGroup(item))) return groups;
                groups.push(group);
                return groups;
            }, []);
        }
    },

    methods: {
        /**
         * Retrieve layout definition from its name
         */
        getLayout(name) {
            if(!this.field.layouts) return;
            return this.field.layouts.find(layout => layout.name == name);
        },

        /**
         * create group instance from raw field value
         */
        getGroup(item) {

            let layout = this.getLayout(item.layout);

            if(!layout) return;

            return new Group(
                layout.name,
                layout.title,
                item.attributes,
                this.field,
                item.key
            );
        },
    }
}
</script>
