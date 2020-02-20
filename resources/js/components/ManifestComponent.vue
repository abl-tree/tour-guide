<template>
  <div>
    <div class="card">
      <div class="card-header">
        Tour Manifest
      </div>
      <div class="card-body">
        <b-row>
          <b-col>
            <strong>{{tour.title}}</strong>
          </b-col>
        </b-row>
        <div class="editor">
          <editor-menu-bar :editor="editor" v-slot="{ commands, isActive, getMarkAttrs }">
            <div class="menubar">

              <button
                class="menubar__button"
                :class="{ 'is-active': isActive.bold() }"
                @click="commands.bold"
              >
                <font-awesome-icon icon="bold"/>
              </button>

              <button
                class="menubar__button"
                :class="{ 'is-active': isActive.italic() }"
                @click="commands.italic"
              >
                <font-awesome-icon icon="italic"/>
              </button>

              <button
                class="menubar__button"
                :class="{ 'is-active': isActive.strike() }"
                @click="commands.strike"
              >
                <font-awesome-icon icon="strikethrough" />
              </button>

              <button
                class="menubar__button"
                :class="{ 'is-active': isActive.underline() }"
                @click="commands.underline"
              >
                <font-awesome-icon icon="underline" />
              </button>

              <button
                class="menubar__button"
                :class="{ 'is-active': isActive.code() }"
                @click="commands.code"
              >
                <font-awesome-icon icon="code" />
              </button>

              <button
                class="menubar__button"
                :class="{ 'is-active': isActive.paragraph() }"
                @click="commands.paragraph"
              >
                <font-awesome-icon icon="paragraph" />
              </button>

              <button
                class="menubar__button"
                :class="{ 'is-active': isActive.heading({ level: 1 }) }"
                @click="commands.heading({ level: 1 })"
              >
                H1
              </button>

              <button
                class="menubar__button"
                :class="{ 'is-active': isActive.heading({ level: 2 }) }"
                @click="commands.heading({ level: 2 })"
              >
                H2
              </button>

              <button
                class="menubar__button"
                :class="{ 'is-active': isActive.heading({ level: 3 }) }"
                @click="commands.heading({ level: 3 })"
              >
                H3
              </button>

              <button
                class="menubar__button"
                :class="{ 'is-active': isActive.bullet_list() }"
                @click="commands.bullet_list"
              >
                <font-awesome-icon icon="list-ul" />
              </button>

              <button
                class="menubar__button"
                :class="{ 'is-active': isActive.ordered_list() }"
                @click="commands.ordered_list"
              >
                <font-awesome-icon icon="list-ol" />
              </button>

              <button
                class="menubar__button"
                :class="{ 'is-active': isActive.blockquote() }"
                @click="commands.blockquote"
              >
                <font-awesome-icon icon="quote-right" />
              </button>

              <button
                class="menubar__button"
                :class="{ 'is-active': isActive.code_block() }"
                @click="commands.code_block"
              >
                <font-awesome-icon icon="code" />
              </button>

              <button
                class="menubar__button"
                @click="commands.horizontal_rule"
              >
                <strong>-</strong>
              </button>

              <button
                class="menubar__button"
                @click="commands.undo"
              >
                <font-awesome-icon icon="undo" />
              </button>

              <button
                class="menubar__button"
                @click="commands.redo"
              >
                <font-awesome-icon icon="redo" />
              </button>

              <!-- <button
                class="menubar__button"
                :class="{ 'is-active': isActive.alignment({ textAlign: 'left' }) }"
                @click="commands.alignment({textAlign: 'left'})"
              >
                left
                <font-awesome-icon icon="redo" />
              </button> -->

              <form class="menubar__form" v-if="linkMenuIsActive" @submit.prevent="setLinkUrl(commands.link, linkUrl)">
                <input class="menubar__input" type="text" v-model="linkUrl" placeholder="https://" ref="linkInput" @keydown.esc="hideLinkMenu"/>
                <button class="menubar__button" @click="setLinkUrl(commands.link, null)" type="button">
                  <font-awesome-icon icon="times" />
                </button>
              </form>

              <template v-else>
                <button
                  class="menubar__button"
                  @click="showLinkMenu(getMarkAttrs('link'))"
                  :class="{ 'is-active': isActive.link() }"
                >
                  <font-awesome-icon icon="link" />
                </button>
              </template>

            </div>
          </editor-menu-bar>
          <hr class="my-4">
          <editor-content class="editor__content" :editor="editor" />
        </div>
        <b-row>
          <b-col>
            <b-button-group size="sm">
              <b-button variant="success" @click="save()">Save</b-button>
              <b-button variant="danger" @click="cancel()">Clear</b-button>
            </b-button-group>
          </b-col>
        </b-row>
      </div>
    </div>
    
    <VuePNotify></VuePNotify>
  </div>
</template>

<style src="vue-pnotify/dist/vue-pnotify.css"></style>

<script>
import Icon from './icon'
import Alignment from '../helpers/alignment'
import { Editor, EditorContent, EditorMenuBar } from 'tiptap'
import {
  Blockquote,
  CodeBlock,
  HardBreak,
  Heading,
  HorizontalRule,
  OrderedList,
  BulletList,
  ListItem,
  TodoItem,
  TodoList,
  Bold,
  Code,
  Italic,
  Link,
  Strike,
  Underline,
  History,
  Placeholder
} from 'tiptap-extensions'

export default {
  components: {
    EditorContent,
    EditorMenuBar,
    Icon,
  },
  props: {
    tour: Object
  },
  data() {
    return {
      editor: new Editor({
        extensions: [
          new Blockquote(),
          new BulletList(),
          new CodeBlock(),
          new HardBreak(),
          new Heading({ levels: [1, 2, 3] }),
          new HorizontalRule(),
          new ListItem(),
          new OrderedList(),
          new TodoItem(),
          new TodoList(),
          new Link(),
          new Bold(),
          new Code(),
          new Italic(),
          new Strike(),
          new Underline(),
          new History(),
          new Alignment(),
          new Placeholder({
            emptyEditorClass: 'is-editor-empty',
            emptyNodeClass: 'is-empty',
            emptyNodeText: 'Write something …',
            showOnlyWhenEditable: true,
            showOnlyCurrent: true,
          }),
        ],
        content: ``,
        onUpdate: ({getJSON, getHTML}) => {
          this.html = getHTML()
        }
      }),
      html: '',
      linkUrl: null,
      linkMenuIsActive: false
    }
  },
  methods: {
    showLinkMenu(attrs) {
      this.linkUrl = attrs.href
      this.linkMenuIsActive = true
      this.$nextTick(() => {
        this.$refs.linkInput.focus()
      })
    },
    hideLinkMenu() {
      this.linkUrl = null
      this.linkMenuIsActive = false
    },
    setLinkUrl(command, url) {
      command({ href: url })
      this.hideLinkMenu()
    },
    save() {
      axios.post('/manifest', {
        'content' : this.html,
        'tour': this.tour
      }).then(data => {
        
        this.$notify({
            title: 'Saved!',
            text: 'Data have been saved.',
            style: 'success'
        })

      })
      .catch(error => {
          let errors = error.response.data.errors

          let tmp = Object.values(errors);

          let errorMessage = tmp.join()

          this.$notify({
              title: 'Error!',
              text: errorMessage,
              style: 'error'
          })
      })
    },
    cancel() {
      this.editor.clearContent(true)
      this.editor.focus()
    }
  },
  beforeDestroy() {
    this.editor.destroy()
  },
  created() {
    // HTML string is also supported
    this.editor.setContent(this.tour && this.tour.manifest ? this.tour.manifest.content : '')

    this.editor.focus()
  }
}
</script>

<style>
.editor {
  max-width: unset;
  min-height: 400px;
  background: aliceblue;
  margin: unset;
}

.editor p.is-editor-empty:first-child::before {
  content: attr(data-empty-text);
  float: left;
  color: #aaa;
  pointer-events: none;
  height: 0;
  font-style: italic;
}
</style>