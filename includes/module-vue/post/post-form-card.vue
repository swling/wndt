<!-- ********************************* 组件结束 ********************************* -->
<div id="app-post-form">
    <form ref="formRef" @submit.prevent="handle_submit" v-if="res.status">
        <div class="field is-horizontal">
            <div class="field-label is-normal">
                <label class="label"></label>
            </div>
            <div class="field-body">
                <thumbnail-card :img-width="80" :img-height="60" :crop-width="400" :crop-height="300" :post_parent="post.ID" meta_key="_thumbnail_id" :thumbnail="thumbnail"></thumbnail-card>
            </div>
        </div>
        <div class="field is-horizontal">
            <div class="field-label is-normal">
                <label class="label">Title</label>
            </div>
            <div class="field-body">
                <input type="text" class="input" v-model="post.post_title" :placeholder="`ID ${post.ID}`" required />
            </div>
        </div>

        <div class="field is-horizontal">
            <div class="field-label is-normal">
                <label class="label">价格</label>
            </div>
            <div class="field-body">
                <post-price-input :post_parent="post.ID" v-model="meta.price"></post-price-input>
            </div>
        </div>

        <div class="field is-horizontal">
            <div class="field-label is-normal"><label class="label is-hidden-mobile">卡券设置</label></div>
            <div class="field-body">
                <div class="field">
                    <a @click="alert_kys_form()" class="button is-warning is-outlined">卡券设置</a>
                </div>
            </div>
        </div>

        <div class="field is-horizontal">
            <div class="field-label is-normal">
                <label class="label">Category</label>
            </div>
            <div class="field-body">
                <multi-level-dropdown :options="category_options" v-model="category_selected" required="1"></multi-level-dropdown>
            </div>
        </div>
        <div class="field is-horizontal">
            <div class="field-label is-normal">
                <label class="label">Tag</label>
            </div>
            <div class="field-body">
                <tags-input :options="tag_options" :max-tags="3" v-model="tag_selected"></tags-input>
            </div>
        </div>

        <div class="field is-horizontal">
            <div class="field-label is-normal">
                <label class="label"></label>
            </div>
            <div class="field-body">
                <rich-editor v-model="post.post_content" v-model:post_id="post.ID" :parent_node="parent_node" v-if="!loading"></rich-editor>
                <div v-else style="height: 100px;"></div>
            </div>
        </div>
        <div class="field is-grouped is-grouped-centered">
            <div class="has-text-centered">
                <button :class="[`button is-${wnd.color.primary}`, { 'is-loading': submitting }]">
                    submit
                </button>
            </div>
        </div>
    </form>
    <div class="notification is-light has-text-centered is-primary" v-show="msg || link" :class="{'is-danger' : has_error}">
        <p v-html="msg" v-show="msg"></p>
        <p v-show="submit_res.status > 0"><span><a :href="link" target="_blank">{{link}}</a></span></p>
    </div>
    <div class="notification is-light has-text-centered is-danger" v-show="`revision`==post.post_type" :class="{'is-danger' : has_error}">
        <p>编辑修订版本 / Edit the revision</p>
    </div>
</div>
<script>
    {
        class MyPostEditor extends PostFormComponent {
            data() {
                let base = super.data();
                let data = {
                    category_options: [],
                    category_selected: [],
                };

                return { ...base, ...data };
            }

            init_data(data) {
                super.init_data(data);

                const categoryData = this.processCatOptions(data.term_options.card_cat, data.terms.card_cat);
                this.category_options = categoryData.options;
                this.category_selected = categoryData.selected;

                const tagData = this.processTagOptions(data.term_options.card_tag, data.terms.card_tag);
                this.tag_options = tagData.options;
                this.tag_selected = tagData.selected;
            }

            get_meta_data() {
                return {
                    _wpmeta_price: this.meta.price,
                };
            }

            get_term_data() {
                return {
                    _term_card_cat: this.category_selected,
                    _term_card_tag: this.tag_selected,
                };
            }

            alert_kys_form() {
                wnd_ajax_modal("wndt_keys_form", { "post_id": this.post.ID });
            }
        }

        const custom = new MyPostEditor("#app-post-form");
        const vueComponent = custom.toVueComponent();
        const app = Vue.createApp(vueComponent);
        app.mount("#app-post-form");

        vueInstances.push(app);
    }
</script>