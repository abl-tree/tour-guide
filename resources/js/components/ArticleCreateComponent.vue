<template>
  <div>
    <b-row>
      <b-col>
        <b-form-input v-model="title" placeholder="Give me a title"></b-form-input>
      </b-col>
    </b-row>
    
    <b-row>
      <b-col>
        <b-form-input v-model="subtitle" placeholder="Give me a sub-title"></b-form-input>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <tiptap-vuetify 
        v-model="content" 
        :extensions="extensions" 
        :toolbar-attributes="{ color: 'blue', dark: true }"
        placeholder="Write Something..."/>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
        <div class="mt-3">
          <b-button-group class="pull-right">
            <b-button variant="success" @click="saveArticle">Save</b-button>
            <b-button variant="danger" @click="cancelArticle">Clear</b-button>
            <b-button variant="info" href="/articles">View All Articles</b-button>
          </b-button-group>
        </div>
      </b-col>
    </b-row>
  </div>
</template>

<script>
import {
  // component
  TiptapVuetify,
  // extensions
  Heading,
  Bold,
  Italic,
  Strike,
  Underline,
  Code,
  Paragraph,
  BulletList,
  OrderedList,
  ListItem,
  Link,
  Blockquote,
  HardBreak,
  HorizontalRule,
  History
} from "tiptap-vuetify";

export default {
  props: {
    article: Object
  },
  components: { TiptapVuetify },
  data: () => ({
    extensions: [
      History,
      Blockquote,
      Link,
      Underline,
      Bold,
      Strike,
      Italic,
      ListItem, // if you need to use a list (BulletList, OrderedList)
      BulletList,
      OrderedList,
      [
        Heading,
        {
          // Options that fall into the tiptap's extension
          options: {
            levels: [1, 2, 3]
          }
        }
      ]
    ],
    title: null,
    subtitle: null,
    content: null
  }),
  methods: {
    saveArticle: function() {
      let params = {
        'title': this.title,
        'subtitle': this.subtitle,
        'content': this.content
      }

      if(this.article) {
        axios.put('/articles/' + this.article.id, params)

        return
      }
      
      axios.post('/articles', params)
    },
    cancelArticle: function() {
      this.title = null
      this.subtitle = null
      this.content = null
    }
  },
  mounted() {
    
    if(this.article) {
      this.title = this.article.title
      this.subtitle = this.article.subtitle
      this.content = this.article.content
    }
    
  }
};
</script>
