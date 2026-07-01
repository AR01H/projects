function fillSpotlight(data) {
    const defaults = {
        term: "Highlights",
        icon: "",
        title: "",
        description: "",
        point_value: "",
        point_label: "",
        link_url: "",
        link_label: "",
        show_link: true,
        sort_order: 0,
        is_active: true
    };

    data = { ...defaults, ...data };

    const set = (name, value) => {
        const el = document.querySelector(`[name="${name}"]`);
        if (el) el.value = value;
    };

    // Select Term
    const termSelect = document.querySelector('[name="term_id"]');
    if (termSelect) {
        const option = [...termSelect.options].find(o =>
            o.textContent.trim().toLowerCase().startsWith(data.term.toLowerCase())
        );
        if (option) termSelect.value = option.value;
    }

    set("icon", data.icon);
    set("title", data.title);
    set("description", data.description);
    set("point_value", data.point_value);
    set("point_label", data.point_label);
    set("link_url", data.link_url);
    set("link_label", data.link_label);
    set("sort_order", data.sort_order);

    const showLink = document.querySelector('[name="show_link"]');
    if (showLink) showLink.checked = !!data.show_link;

    const active = document.querySelector('[name="is_active"]');
    if (active) active.checked = !!data.is_active;

    console.log("✅ Spotlight form filled.");
}


fillSpotlight({
    icon: "🏦",
    title: "Mortgage Ready",
    description: "Secure an Agreement in Principle before house hunting.",
    point_value: "95%",
    point_label: "Maximum LTV"
});