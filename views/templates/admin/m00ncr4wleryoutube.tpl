<div id="product-youtube" class="panel product-tab">
    <input type="hidden" name="submitted_tabs[]" value="Youtube"/>

    <h3>{l s='Youtube' mod='m00ncr4wleryoutube'}</h3>

    <div class="form-group">
        <label class="control-label col-lg-3">
            {l s='URL' mod='m00ncr4wleryoutube'}
        </label>

        <div class="col-lg-9">
            {include file="controllers/products/input_text_lang.tpl" languages=$languages input_value=$youtube input_name="youtube" maxchar=255}
        </div>
    </div>

    <div class="panel-footer">
        <a href="{$link->getAdminLink('AdminProducts')}" class="btn btn-default"><i
                    class="process-icon-cancel"></i> {l s='Cancel'}</a>
        <button type="submit" name="submitAddproduct" class="btn btn-default pull-right"><i
                    class="process-icon-save"></i> {l s='Save'}</button>
        <button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right"><i
                    class="process-icon-save"></i> {l s='Save and stay'}</button>
    </div>

    <script type="text/javascript">
        hideOtherLanguage({$default_form_language});
    </script>
</div>