import { apiFetch } from "../utils/network.js";
export function News({
  initialNewsPosts,
  isAdmin
}) {
  const [activePostId, setActivePostId] = React.useState(initialNewsPosts[0] !== "undefined" ? initialNewsPosts[0].post_id : null);
  const [editPostId, setEditPostId] = React.useState(null);
  const [numPosts, setNumPosts] = React.useState(initialNewsPosts.length);
  const [newsPosts, setNewsPosts] = React.useState(initialNewsPosts);
  const titleRef = React.useRef(null);
  const versionRef = React.useRef(null);
  const contentRef = React.useRef(null);
  const updateTagRef = React.useRef(null);
  const bugfixTagRef = React.useRef(null);
  const eventTagRef = React.useRef(null);

  function formatNewsDate(ticks) {
    const date = new Date(ticks * 1000);
    return date.toLocaleDateString('en-US', {
      month: '2-digit',
      day: '2-digit',
      year: '2-digit'
    });
  }

  function cleanNewsContents(contents) {
    console.log(contents);
    const parser = new DOMParser();
    return parser.parseFromString(contents.replace(/[\r\n]+/g, " ").replace(/<br\s*\/?>/g, '\n'), 'text/html').body.textContent;
  }

  function saveNewsItem(postId) {
    console.log(contentRef.current.value);
    apiFetch(homeLinks.news_api, {
      request: 'saveNewsPost',
      post_id: postId,
      title: titleRef.current.textContent,
      version: versionRef.current.textContent,
      content: contentRef.current.value,
      update: updateTagRef.current.checked,
      bugfix: bugfixTagRef.current.checked,
      event: eventTagRef.current.checked,
      num_posts: numPosts
    }).then(response => {
      if (response.errors.length) {
        console.warn(response.errors);
      } else {
        setNewsPosts(response.data.postData);
      }
    });
    setEditPostId(null);
  }

  function NewsItem({
    newsItem
  }) {
    return /*#__PURE__*/React.createElement("div", {
      className: "news_item"
    }, /*#__PURE__*/React.createElement("div", {
      className: activePostId === newsItem.post_id ? "news_item_header" : "news_item_header news_item_header_minimized",
      onClick: () => setActivePostId(newsItem.post_id)
    }, /*#__PURE__*/React.createElement("div", {
      className: "news_item_title"
    }, newsItem.title.toUpperCase()), /*#__PURE__*/React.createElement("div", {
      className: "news_item_version"
    }, newsItem.version && newsItem.version.toUpperCase()), newsItem.tags.map((tag, index) => /*#__PURE__*/React.createElement("div", {
      key: index,
      className: "news_item_tag_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_divider"
    }, "/"), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag"
    }, tag.toUpperCase()))), /*#__PURE__*/React.createElement("div", {
      className: "news_item_details_container"
    }, isAdmin && /*#__PURE__*/React.createElement("div", {
      className: "news_item_edit",
      onClick: () => setEditPostId(newsItem.post_id)
    }, "EDIT"), /*#__PURE__*/React.createElement("div", {
      className: "news_item_details"
    }, "POSTED ", formatNewsDate(newsItem.time), " BY ", newsItem.sender.toUpperCase()))), activePostId === newsItem.post_id && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
      className: "news_item_banner"
    }), /*#__PURE__*/React.createElement("div", {
      className: "news_item_content",
      dangerouslySetInnerHTML: {
        __html: newsItem.message
      }
    })));
  }

  function NewsItemEdit({
    newsItem
  }) {
    return /*#__PURE__*/React.createElement("div", {
      className: "news_item_editor"
    }, /*#__PURE__*/React.createElement("div", {
      className: activePostId === newsItem.post_id ? "news_item_header" : "news_item_header news_item_header_minimized",
      onClick: () => setActivePostId(newsItem.post_id)
    }, /*#__PURE__*/React.createElement("div", {
      className: "news_item_title",
      ref: titleRef,
      contentEditable: "true",
      suppressContentEditableWarning: true
    }, newsItem.title.toUpperCase()), /*#__PURE__*/React.createElement("div", {
      className: "news_item_version",
      ref: versionRef,
      contentEditable: "true",
      suppressContentEditableWarning: true
    }, newsItem.version && newsItem.version.toUpperCase()), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_divider"
    }, "/"), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag"
    }, "UPDATE"), /*#__PURE__*/React.createElement("input", {
      id: "news_tag_update",
      type: "checkbox",
      ref: updateTagRef,
      defaultChecked: newsItem.tags.includes("update")
    })), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_divider"
    }, "/"), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag"
    }, "BUG FIXES"), /*#__PURE__*/React.createElement("input", {
      id: "news_tag_bugfixes",
      type: "checkbox",
      ref: bugfixTagRef,
      defaultChecked: newsItem.tags.includes("bugfix")
    })), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_container"
    }, /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag_divider"
    }, "/"), /*#__PURE__*/React.createElement("div", {
      className: "news_item_tag"
    }, "EVENT"), /*#__PURE__*/React.createElement("input", {
      id: "news_tag_event",
      type: "checkbox",
      ref: eventTagRef,
      defaultChecked: newsItem.tags.includes("event")
    })), /*#__PURE__*/React.createElement("div", {
      className: "news_item_details_container"
    }, isAdmin && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
      className: "news_item_edit",
      onClick: () => setEditPostId(null)
    }, "CANCEL"), /*#__PURE__*/React.createElement("div", {
      className: "news_item_edit",
      onClick: () => saveNewsItem(newsItem.post_id)
    }, "SAVE")), /*#__PURE__*/React.createElement("div", {
      className: "news_item_details"
    }, "POSTED ", formatNewsDate(newsItem.time), " BY ", newsItem.sender.toUpperCase()))), activePostId === newsItem.post_id && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", {
      className: "news_item_banner"
    }), /*#__PURE__*/React.createElement("textarea", {
      className: "news_item_content_editor",
      ref: contentRef,
      defaultValue: cleanNewsContents(newsItem.message)
    })));
  }

  return /*#__PURE__*/React.createElement("div", {
    className: "news_posts_container"
  }, newsPosts && newsPosts.map(newsItem => newsItem.post_id === editPostId ? /*#__PURE__*/React.createElement(NewsItemEdit, {
    key: `news_post:${newsItem.post_id}`,
    newsItem: newsItem
  }) : /*#__PURE__*/React.createElement(NewsItem, {
    key: `news_post:${newsItem.post_id}`,
    newsItem: newsItem
  })));
}