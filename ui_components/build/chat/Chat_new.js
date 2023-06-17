import { apiFetch } from "../utils/network.js";
const chatRefreshInterval = 5000;
function Chat({
  chatApiLink,
  initialPosts,
  initialPostId,
  initialNextPagePostId,
  initialPreviousPagePostId,
  initialLatestPostId,
  maxPostLength,
  isModerator,
  initialBanInfo,
  memes
}) {
  const [banInfo, setBanInfo] = React.useState(initialBanInfo);
  const [posts, setPosts] = React.useState(initialPosts);
  const [nextPagePostId, setNextPagePostId] = React.useState(initialNextPagePostId);
  const [latestPostId, setLatestPostId] = React.useState(initialLatestPostId);
  // Only set if we're paginating
  const [previousPagePostId, setPreviousPagePostId] = React.useState(initialPreviousPagePostId);
  const [highlightPostId, setHighlightPostId] = React.useState(initialPostId);
  const currentPagePostIdRef = React.useRef(initialPostId);
  const [error, setError] = React.useState(null);
  const [message, setMessage] = React.useState("");
  if (banInfo.isBanned) {
    return /*#__PURE__*/React.createElement(ChatBanInfo, {
      banName: banInfo.name,
      banDescription: banInfo.description,
      banTimeRemaining: banInfo.timeRemaining
    });
  }
  const refreshChat = function () {
    if (currentPagePostIdRef.current != null) {
      // always refresh when initialized to latest
      if (currentPagePostIdRef.current != latestPostId) {
        return;
      }
    }
    apiFetch(chatApiLink, {
      request: 'load_posts'
    }).then(handleApiResponse);
  };
  React.useEffect(() => {
    const intervalId = setInterval(refreshChat, chatRefreshInterval);
    return () => clearInterval(intervalId);
  }, []);
  function submitPost(message) {
    apiFetch(chatApiLink, {
      request: 'submit_post',
      message: message
    }).then(handleApiResponse);
  }
  function deletePost(postId) {
    apiFetch(chatApiLink, {
      request: 'delete_post',
      post_id: postId
    }).then(handleApiResponse);
  }
  function changePage(newStartingPostId) {
    if (newStartingPostId === currentPagePostIdRef.current) {
      return;
    }
    if (newStartingPostId == null || newStartingPostId >= latestPostId) {
      currentPagePostIdRef.current = null;
      apiFetch(chatApiLink, {
        request: 'load_posts'
      }).then(handleApiResponse);
      return;
    }
    if (newStartingPostId === currentPagePostIdRef.current) {
      return;
    }
    currentPagePostIdRef.current = newStartingPostId;
    apiFetch(chatApiLink, {
      request: 'load_posts',
      starting_post_id: newStartingPostId
    }).then(handleApiResponse);
  }
  const handleApiResponse = response => {
    if (response.data.banInfo && response.data.banInfo.isBanned) {
      setBanInfo(response.data.banInfo);
      return;
    }
    if (response.errors.length > 0) {
      setError(response.errors.join(' '));
    } else {
      setError(null);
    }
    if (response.data.posts != null) {
      setPosts(response.data.posts);
    }
    if (response.data.latestPostId != null) {
      setLatestPostId(response.data.latestPostId);
    }
    if (typeof response.data.previousPagePostId != "undefined") {
      setPreviousPagePostId(response.data.previousPagePostId);
    }
    if (typeof response.data.nextPagePostId != "undefined") {
      setNextPagePostId(response.data.nextPagePostId);
    }
  };
  function quotePost(postId) {
    setMessage(prevMessage => `${prevMessage}[quote:${postId}]`);
  }
  return /*#__PURE__*/React.createElement("div", null, error != null && /*#__PURE__*/React.createElement("p", {
    className: "systemMessage"
  }, error), /*#__PURE__*/React.createElement(ChatInput, {
    maxPostLength: maxPostLength,
    memes: memes,
    submitPost: submitPost,
    message: message,
    setMessage: setMessage
  }), /*#__PURE__*/React.createElement(ChatPosts, {
    posts: posts,
    previousPagePostId: previousPagePostId,
    nextPagePostId: nextPagePostId,
    isModerator: isModerator,
    deletePost: deletePost,
    quotePost: quotePost,
    goToNextPage: () => changePage(nextPagePostId),
    goToPreviousPage: () => changePage(previousPagePostId),
    goToLatestPage: () => changePage(latestPostId),
    postsBehind: currentPagePostIdRef.current != null ? latestPostId - currentPagePostIdRef.current : 0
  }));
}
function ChatBanInfo({
  banName,
  banDescription,
  banTimeRemaining
}) {
  return /*#__PURE__*/React.createElement("table", {
    className: "table"
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, banName)), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
    style: {
      textAlign: "center"
    }
  }, banDescription, banTimeRemaining != null && /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("b", null, "Time Remaining:"), " ", banTimeRemaining), /*#__PURE__*/React.createElement("br", null), "Visit the ", /*#__PURE__*/React.createElement("a", {
    href: "/support.php"
  }, "Support Center"), " to appeal this."))));
}
function ChatInput({
  maxPostLength,
  memes,
  submitPost,
  message,
  setMessage
}) {
  const [quickReply, _setQuickReply] = React.useState(JSON.parse(localStorage.getItem("quick_reply_on") ?? "true"));
  const [showMemeSelect, setShowMemeSelect] = React.useState(false);
  function setQuickReply(newValue) {
    localStorage.setItem("quick_reply_on", JSON.stringify(newValue));
    _setQuickReply(newValue);
  }
  function handleMemeSelect(memeIndex) {
    setMessage(prevMessage => `${prevMessage}${memes.codes[memeIndex]}`);
    setShowMemeSelect(false);
  }
  const handlePostSubmit = React.useCallback(() => {
    submitPost(message);
    // TODO: Only clear this on a successful server response
    setMessage("");
  }, [message, submitPost]);
  const handleKeyDown = React.useCallback(e => {
    if (e.code !== "Enter") {
      return;
    }
    if (quickReply && !e.shiftKey) {
      e.preventDefault();
      setMessage(e.target.value);
      handlePostSubmit();
    }
  }, [quickReply, handlePostSubmit]);
  const charactersRemaining = maxPostLength - message.length;
  const charactersRemainingDisplay = `Characters remaining ${charactersRemaining}`;
  return /*#__PURE__*/React.createElement("div", {
    className: "chat_input_container"
  }, showMemeSelect && /*#__PURE__*/React.createElement(ChatMemeModal, {
    memes: memes,
    selectMeme: handleMemeSelect,
    closeMemeSelect: () => setShowMemeSelect(false)
  }), /*#__PURE__*/React.createElement("div", {
    className: "chat_input_left"
  }, /*#__PURE__*/React.createElement("div", {
    className: "chat_settings_container"
  }, /*#__PURE__*/React.createElement("label", {
    className: "quick_reply_label ft-s ft-c1 ft-medium"
  }, "quick-reply"), /*#__PURE__*/React.createElement("input", {
    className: "quick_reply_box",
    type: "checkbox",
    checked: quickReply,
    onChange: e => setQuickReply(e.target.checked)
  })), /*#__PURE__*/React.createElement("div", {
    className: "chat_meme_toggle_container"
  }, /*#__PURE__*/React.createElement("button", {
    className: "meme_toggle button-bar_large ft-s ft-c1 ft-medium t-hover",
    onClick: () => setShowMemeSelect(!showMemeSelect)
  }, "meme list"))), /*#__PURE__*/React.createElement("div", {
    className: "chat_input_center"
  }, /*#__PURE__*/React.createElement("textarea", {
    id: "chat_input_box",
    minLength: "3",
    maxLength: maxPostLength,
    value: message,
    onChange: e => setMessage(e.target.value),
    onKeyDown: handleKeyDown
  }), /*#__PURE__*/React.createElement("span", {
    className: "ft-s ft-c1"
  }, charactersRemainingDisplay)), /*#__PURE__*/React.createElement("div", {
    className: "chat_input_right"
  }, /*#__PURE__*/React.createElement("button", {
    className: "chat_submit_button button-bar_large ft-s ft-c1 ft-medium t-hover",
    onClick: handlePostSubmit
  }, "post")));
}
function ChatMemeModal({
  memes,
  selectMeme,
  closeMemeSelect
}) {
  return /*#__PURE__*/React.createElement("div", {
    id: "meme_modal"
  }, /*#__PURE__*/React.createElement("div", {
    className: "meme_modal_header"
  }), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
    id: "meme_box"
  }, memes.codes.map((meme, i) => /*#__PURE__*/React.createElement("div", {
    key: `meme:${i}`,
    className: "meme_select"
  }, /*#__PURE__*/React.createElement("img", {
    src: memes.urls[i],
    onClick: () => selectMeme(i)
  }))))), /*#__PURE__*/React.createElement("div", {
    className: "meme_modal_footer"
  }, /*#__PURE__*/React.createElement("button", {
    className: "meme_close button-bar_large ft-s ft-c1 ft-medium t-hover",
    onClick: closeMemeSelect
  }, "close")));
}
function ChatPosts({
  posts,
  previousPagePostId,
  nextPagePostId,
  isModerator,
  deletePost,
  quotePost,
  goToPreviousPage,
  goToNextPage,
  goToLatestPage,
  postsBehind
}) {
  return /*#__PURE__*/React.createElement(React.Fragment, null, "  ", postsBehind > 200 && /*#__PURE__*/React.createElement("div", {
    id: "chat_navigation_latest"
  }, /*#__PURE__*/React.createElement("a", {
    className: "chat_pagination",
    onClick: goToLatestPage
  }, "<< Jump To Latest >>")), /*#__PURE__*/React.createElement("div", {
    id: "chat_navigation_top"
  }, /*#__PURE__*/React.createElement("div", {
    className: "chat_navigation_divider_left"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "100%",
    height: "2"
  }, /*#__PURE__*/React.createElement("line", {
    x1: "0%",
    y1: "1",
    x2: "100%",
    y2: "1",
    stroke: "#4e4535",
    strokeWidth: "1"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "chat_pagination_wrapper"
  }, previousPagePostId != null && /*#__PURE__*/React.createElement("a", {
    className: "chat_pagination",
    onClick: goToPreviousPage
  }, "<< Newer")), /*#__PURE__*/React.createElement("div", {
    className: "chat_navigation_divider_middle"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "100%",
    height: "2"
  }, /*#__PURE__*/React.createElement("line", {
    x1: "0%",
    y1: "1",
    x2: "100%",
    y2: "1",
    stroke: "#4e4535",
    strokeWidth: "1"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "chat_pagination_wrapper"
  }, nextPagePostId != null && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
    className: "chat_pagination",
    onClick: goToNextPage
  }, "Older >>"))), /*#__PURE__*/React.createElement("div", {
    className: "chat_navigation_divider_right"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "100%",
    height: "2"
  }, /*#__PURE__*/React.createElement("line", {
    x1: "0%",
    y1: "1",
    x2: "100%",
    y2: "1",
    stroke: "#4e4535",
    strokeWidth: "1"
  })))), /*#__PURE__*/React.createElement("div", {
    className: "chat_post_container"
  }, posts.length < 1 && /*#__PURE__*/React.createElement("div", {
    style: {
      textAlign: "center"
    }
  }, "No posts!"), posts.map((post, i) => /*#__PURE__*/React.createElement("div", {
    key: `post:${i}`,
    className: "chat_post",
    style: {
      textAlign: "center"
    }
  }, /*#__PURE__*/React.createElement("div", {
    className: "post_user_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "post_user_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "post_user_avatar_wrapper " + post.avatarStyle + " " + post.userLinkClassNames.join(' ')
  }, /*#__PURE__*/React.createElement("img", {
    className: "post_user_avatar_img " + post.avatarStyle,
    src: post.avatarLink
  })), /*#__PURE__*/React.createElement("div", {
    className: "post_user_info"
  }, /*#__PURE__*/React.createElement("a", {
    href: post.userProfileLink,
    className: "chat_user_name " + post.userLinkClassNames.join(' ')
  }, post.userName), /*#__PURE__*/React.createElement("div", {
    className: "post_village_title"
  }, post.userTitle.toLowerCase(), /*#__PURE__*/React.createElement("img", {
    className: "chat_village_icon",
    src: `./images/village_icons/${post.userVillage.toLowerCase()}.png`,
    alt: `${post.userVillage} Village`,
    title: `${post.userVillage} Village`
  })), post.staffBannerName && /*#__PURE__*/React.createElement("div", {
    className: "post_staff_title",
    style: {
      backgroundColor: post.staffBannerColor
    }
  }, post.staffBannerName)))), /*#__PURE__*/React.createElement("div", {
    className: "post_message_container ft-s ft-c3 ft-default",
    "data-id": post.id,
    dangerouslySetInnerHTML: {
      __html: post.message
    }
  }), /*#__PURE__*/React.createElement("div", {
    className: "post_controls_container ft-s ft-c3"
  }, /*#__PURE__*/React.createElement("div", {
    className: "post_controls_wrapper"
  }, /*#__PURE__*/React.createElement("div", {
    className: "post_quote_wrapper"
  }, /*#__PURE__*/React.createElement("img", {
    className: "post_quote",
    src: "../images/v2/icons/quote_hover.png",
    onClick: () => quotePost(post.id)
  })), isModerator && /*#__PURE__*/React.createElement("div", {
    className: "post_delete_wrapper"
  }, /*#__PURE__*/React.createElement("img", {
    className: "post_delete",
    src: "../images/v2/icons/delete_hover.png",
    onClick: () => deletePost(post.id)
  })), /*#__PURE__*/React.createElement("div", {
    className: "post_report_wrapper"
  }, /*#__PURE__*/React.createElement("a", {
    className: "imageLink",
    href: post.reportLink
  }, /*#__PURE__*/React.createElement("img", {
    className: "post_report",
    src: "../images/v2/icons/report_hover.png"
  })))), /*#__PURE__*/React.createElement("div", {
    className: "post_timestamp"
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      marginBottom: "2px"
    }
  }, post.timeString)))))), /*#__PURE__*/React.createElement("div", {
    id: "chat_navigation_bottom"
  }, /*#__PURE__*/React.createElement("div", {
    className: "chat_navigation_divider_left"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "100%",
    height: "2"
  }, /*#__PURE__*/React.createElement("line", {
    x1: "0%",
    y1: "1",
    x2: "100%",
    y2: "1",
    stroke: "#4e4535",
    strokeWidth: "1"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "chat_pagination_wrapper"
  }, previousPagePostId != null && /*#__PURE__*/React.createElement("a", {
    className: "chat_pagination",
    onClick: goToPreviousPage
  }, "<< Newer")), /*#__PURE__*/React.createElement("div", {
    className: "chat_navigation_divider_middle"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "100%",
    height: "2"
  }, /*#__PURE__*/React.createElement("line", {
    x1: "0%",
    y1: "1",
    x2: "100%",
    y2: "1",
    stroke: "#4e4535",
    strokeWidth: "1"
  }))), /*#__PURE__*/React.createElement("div", {
    className: "chat_pagination_wrapper"
  }, nextPagePostId != null && /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("a", {
    className: "chat_pagination",
    onClick: goToNextPage
  }, "Older >>"))), /*#__PURE__*/React.createElement("div", {
    className: "chat_navigation_divider_right"
  }, /*#__PURE__*/React.createElement("svg", {
    width: "100%",
    height: "2"
  }, /*#__PURE__*/React.createElement("line", {
    x1: "0%",
    y1: "1",
    x2: "100%",
    y2: "1",
    stroke: "#4e4535",
    strokeWidth: "1"
  })))));
}
window.Chat = Chat;