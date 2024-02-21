export function parseKeywords(text) {
  const parts = text.split(/\[keyword\]|\[\/keyword\]/);
  return parts.map((part, index) => {
    // Even indices (0, 2, 4...) are outside the keywords, odd indices (1, 3, 5...) are the keywords
    if (index % 2 === 1) {
      return /*#__PURE__*/React.createElement("span", {
        className: "keyword",
        key: index
      }, part);
    } else {
      return part;
    }
  });
}