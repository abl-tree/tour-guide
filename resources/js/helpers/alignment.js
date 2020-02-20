import { toggleBlockType, toggleMark } from 'tiptap-commands';
import { Mark } from 'tiptap';

export default class Alignment extends Mark {
	get name() {
		return 'alignment';
	}

	get schema() {
		return {
			attrs: {
				textAlign: {
					default: 'left'
				}
			},
			content: 'inline*',
			group: 'block',
			draggable: false,
			parseDOM: [
				{
					style: 'text-align',
					getAttrs: (node) => ({
						textAlign: node.style.textAlign || 'left'
					})
				}
			],
			toDOM: (node) => [ 'div', { style: `text-align: ${node.attrs.textAlign}` }, 0 ]
		};
	}

	commands({ type, schema }) {
        return (attrs) => toggleMark(type, attrs)
		return (attrs) => toggleBlockType(type, schema.nodes.paragraph, attrs);
	}
}