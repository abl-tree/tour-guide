import { Mark } from "tiptap";
import { updateMark, markInputRule, removeMark, toggleBlockType, toggleMark } from "tiptap-commands";

export default class CustomHeading extends Mark {
  get name() {
    return "custom_heading";
  }

  get defaultOptions() {
    return {
        fontSize: [1.575, 1.8, 2.25, 4, 5, 6],
    }
  }

  get schema() {
    return {
      attrs: {
        fontSize: {
          default: 1.575
        }
      },
      content: "inline*",
      group: "block",
      draggable: false,
      parseDOM: [
        {
          style: "font-size",
          getAttrs: (mark) => ({ fontSize: mark })
        }
      ],
      toDOM: (mark) => [
        "span",
        { style: `font-size: ${mark.attrs.fontSize}rem` },
        0
      ]
    };
  }

  commands({ type, schema }) {
    return (attrs) => {
      if (attrs.fontSize) {
        return toggleMark(type, attrs);
      }
      return removeMark(type);
    };
  }

  inputRules({ type }) {
    return [markInputRule(/(?:\*\*|__)([^*_]+)(?:\*\*|__)$/, type)];
  }
}   