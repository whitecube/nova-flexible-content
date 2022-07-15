export default class Group {

    constructor(name, title, fields, field, key, collapsed = true) {
        this.name = name;
        this.title = title;
        this.fields = fields;
        this.key = key || this.getTemporaryUniqueKey(field.attribute);
        this.collapsed = collapsed;
        this.readonly = field.readonly;

        this.renameFields();
    }

    /**
     * Retrieve the layout's filled FormData
     */
    values() {
        let formData = new FormData();

        for (var i = 0; i < this.fields.length; i++) {
            this.fields[i].fill(formData);
        }

        return formData;
    }

    /**
     * Retrieve the layout's filled object
     */
    serialize() {
        let data = {
            layout: this.name,
            key: this.key,
            attributes: {},
            files: {}
        };

        for(var item of this.values()) {
            if(item[0].indexOf('___upload-') == 0) {
                // Previously nested file attribute
                data.files[item[0]] = item[1];
                continue;
            }

            if(!(item[1] instanceof File || item[1] instanceof Blob)) {
                // Simple input value, no need to attach files
                data.attributes[item[0]] = item[1];
                continue;
            }

            // File object, attach its file for upload
            data.attributes[item[0]] = '___upload-' + item[0];
            data.files['___upload-' + item[0]] = item[1];
        }

        return data;
    }

    /**
     * Generate a unique string for current group
     */
    getTemporaryUniqueKey(attribute) {
        return this.randomString(16);
    }

    randomString(len, charSet) {
        charSet = charSet || 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var randomString = '';
        for (var i = 0; i < len; i++) {
            var randomPoz = Math.floor(Math.random() * charSet.length);
            randomString += charSet.substring(randomPoz,randomPoz+1);
        }
        return randomString;
    }

    /**
     * Assign a new unique field name to each field
     */
    renameFields() {
        for (var i = this.fields.length - 1; i >= 0; i--) {
            this.fields[i].attribute = this.key + '__' + this.fields[i].attribute;
            this.fields[i].validationKey = this.fields[i].attribute;

            if (this.fields[i].dependsOn) {
                Object.keys(this.fields[i].dependsOn).forEach(key => {
                    this.fields[i].dependsOn[`${this.key}__${key}`] = this.fields[i].dependsOn[key];
                    delete this.fields[i].dependsOn[key];
                });
            }
        }
    }

}
