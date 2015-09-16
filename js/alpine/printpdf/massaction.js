varienGridMassaction.addMethods({
    apply: function() {
        if(varienStringArray.count(this.checkedString) == 0) {
            alert(this.errorText);
            return;
        }

        var item = this.getSelectedItem();
        if(!item) {
            this.validator.validate();
            return;
        }
        this.currentItem = item;
        var fieldName = (item.field ? item.field : this.formFieldName);
        var fieldsHtml = '';

        if(this.currentItem.confirm && !window.confirm(this.currentItem.confirm)) {
            return;
        }

        this.formHiddens.update('');
        new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name: fieldName, value: this.checkedString}));
        new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name: 'massaction_prepare_key', value: fieldName}));

        if(!this.validator.validate()) {
            return;
        }

        if(this.useAjax && item.url) {
            new Ajax.Request(item.url, {
                'method': 'post',
                'parameters': this.form.serialize(true),
                'onComplete': this.onMassactionComplete.bind(this)
            });
        } else if(item.url) {
            this.form.action = item.url;
            var oldTarget = this.form.target,
                form      = this.form;
            if (item.target) {
                form.target = item.target;
            }
            form.submit();
            setTimeout(function() {
                form.target = oldTarget;
            }, 10);
        }
    }
});