<template>
    <PanelItem :index="index" :field="field">
        <template #value>
            <div v-for="(group, index) in orderedGroups">
                <detail-nova-flexible-content-group
                    :index="index"
                    :last="(index === groups.length - 1)"
                    :group="group"
                    :key="group.key"
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
    data: () => ({
        order: [],
        groups: {},
    }),

    computed: {
        layouts() {
            return this.field.layouts || false;
        },
        orderedGroups() {
            this.value = this.field.value || [];

            this.populateGroups();

            return this.order.reduce((groups, key) => {
                groups.push(this.groups[key]);
                return groups;
            }, []);
        },
    },

    methods: {
        /*
            * Set the displayed layouts from the field's current value
            */
        populateGroups() {
            this.order.splice(0, this.order.length);
            this.groups = {};

            for (var i = 0; i < this.value.length; i++) {
                this.addGroup(
                    this.getLayout(this.value[i].layout),
                    this.value[i].attributes,
                    this.value[i].key,
                    this.field.collapsed,
                    this.value[i].title_data
                );
            }
        },

        /**
         * Retrieve layout definition from its name
         */
        getLayout(name) {
            if (!this.layouts) return;
            return this.layouts.find((layout) => layout.name == name);
        },

        /**
         * Append the given layout to flexible content's list
         */
        addGroup(layout, attributes, key, collapsed, resolved_title) {
            if (!layout) return;

            collapsed = collapsed || false;

            let fields =
                    attributes || JSON.parse(JSON.stringify(layout.fields)),
                group = new Group(
                    layout.name,
                    layout.title,
                    fields,
                    this.field,
                    key,
                    collapsed,
                    layout,
                    resolved_title
                );

            this.groups[group.key] = group;
            this.order.push(group.key);
        },
    },
};
</script>
