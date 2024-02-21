export function parseKeywords(text) {
    const tagStyles = {
        keyword: { className: 'keyword' }, // No additional text for 'keyword'
        quote: { className: 'quote', beforeText: '"', afterText: '"' }, // Adding quotation marks for 'quote'
    };

    const tagRegex = new RegExp(`\\[(\\/)?(${Object.keys(tagStyles).join('|')})\\]`, 'g');
    const parts = text.split(tagRegex);
    const processedParts = [];
    const tagStack = [];

    parts.forEach((part, index) => {
        if (index % 3 === 0) {
            // Regular text
            if (part) {
                if (tagStack.length > 0) {
                    const currentTag = tagStack[tagStack.length - 1];
                    const tagStyle = tagStyles[currentTag];
                    const className = tagStyle.className;
                    // Prepare content with optional before and after text
                    const content = [(tagStyle.beforeText || ''), part, (tagStyle.afterText || '')].join('');
                    // Create a React span element with the class name and prepared content
                    const element = React.createElement('span', { key: index, className: className }, content);
                    processedParts.push(element);
                } else {
                    processedParts.push(part);
                }
            }
        } else if (index % 3 === 1) {
            // This part is for matching the pattern, no content change
        } else if (index % 3 === 2) {
            // Tag name handling for opening or closing tags
            if (parts[index - 1]) {
                tagStack.pop(); // Closing tag
            } else {
                tagStack.push(part); // Opening tag
            }
        }
    });

    return <>{processedParts}</>;
}