<template>
  <div>
    <b-row>
      <b-col>
        <b-form-input v-model="title" placeholder="Give me a title"></b-form-input>
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
  components: { TiptapVuetify },
  data: () => ({
    extensions: [
      History,
      Blockquote,
      Link,
      Underline,
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
      ],
      Bold,
      Link,
      Code,
      HorizontalRule,
      Paragraph,
      HardBreak // line break on Shift + Ctrl + Enter
    ],
    title: null,
    content: `
      <h1>Most basic use</h1>
      <p>
        You can use the necessary extensions. 
        The corresponding buttons are 
        <strong>
          added automatically.
        </strong>
      </p>
      <pre><code>&lt;tiptap-vuetify v-model="content" :extensions="extensions"/&gt;</code></pre>
      <p></p>
      <h2>Icons</h2>
      <p>Avaliable icons groups:</p>
      <ol>
        <li>
          <p>Material Design <em>Official</em></p>
        </li>
        <li>
          <p>Font Awesome (FA)</p>
        </li>
        <li>
          <p>Material Design Icons (MDI)</p>
        </li>
      </ol>
      <p></p>
      <blockquote>
        <p>This package is awesome!</p>
      </blockquote>
      <p></p>
    `
  }),
  methods: {
    saveArticle: function() {
      let params = {
        'title': this.title,
        'content': this.content
      }
      
      axios.post('/articles', params)
    },
    cancelArticle: function() {
      this.title = null
      this.content = null
    }
  }
};
</script>
