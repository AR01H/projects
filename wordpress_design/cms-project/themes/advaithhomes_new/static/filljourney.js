function fillJourney(data) {

    const set = (selector, value) => {
        const el = document.querySelector(selector);
        if (el) {
            el.value = value ?? "";
            el.dispatchEvent(new Event("input", { bubbles: true }));
            el.dispatchEvent(new Event("change", { bubbles: true }));
        }
    };

    set('[name="journey[heading]"]', data.heading);
    set('[name="journey[tip_icon]"]', data.tip_icon);
    set('[name="journey[tip_text]"]', data.tip_text);
    set('[name="journey[tip_link_label]"]', data.tip_link_label);
    set('[name="journey[tip_link_url]"]', data.tip_link_url);

    const wrap = document.querySelector("#journey-steps-wrap");
    const addBtn = document.querySelector('.adn-rep-add[data-prefix="journey[steps]"]');

    if (!wrap || !addBtn) {
        console.error("Journey repeater not found.");
        return;
    }

    wrap.innerHTML = "";

    let index = 0;

    function fillNext() {

        if (index >= data.steps.length) {
            console.log("✅ Journey populated.");
            return;
        }

        addBtn.click();

        setTimeout(() => {

            const item = wrap.lastElementChild;

            if (!item) {
                console.error("Unable to find new step.");
                return;
            }

            item.querySelectorAll("input,textarea").forEach(field => {

                const name = (field.name || "").toLowerCase();

                if (name.includes("icon")) {
                    field.value = data.steps[index].icon || "";
                }
                else if (name.includes("label") || name.includes("step")) {
                    field.value = data.steps[index].label || "";
                }
                else if (name.includes("title")) {
                    field.value = data.steps[index].title || "";
                }
                else if (
                    name.includes("description") ||
                    name.includes("desc") ||
                    name.includes("text")
                ) {
                    field.value = data.steps[index].description || "";
                }

                field.dispatchEvent(new Event("input", { bubbles: true }));
                field.dispatchEvent(new Event("change", { bubbles: true }));
            });

            index++;
            fillNext();

        }, 150);

    }

    fillNext();
}
fillJourney({
    heading: "Your Home Buying Journey",

    steps: [
        {
            icon: "💰",
            label: "Step 1",
            title: "Save Deposit",
            description: "Save 5–20% plus buying costs."
        },
        {
            icon: "🏦",
            label: "Step 2",
            title: "Mortgage Ready",
            description: "Get an Agreement in Principle."
        },
        {
            icon: "🏡",
            label: "Step 3",
            title: "Find Property",
            description: "View homes and make an offer."
        },
        {
            icon: "⚖️",
            label: "Step 4",
            title: "Instruct Solicitor",
            description: "Start conveyancing and legal searches."
        },
        {
            icon: "📋",
            label: "Step 5",
            title: "Survey Property",
            description: "Check the home's condition."
        },
        {
            icon: "✍️",
            label: "Step 6",
            title: "Exchange Contracts",
            description: "Pay deposit and exchange contracts."
        },
        {
            icon: "🔑",
            label: "Step 7",
            title: "Complete Purchase",
            description: "Collect your keys and move in."
        }
    ],

    tip_icon: "💡",
    tip_text: "Most UK purchases complete within 12–16 weeks.",
    tip_link_label: "View Timeline →",
    tip_link_url: "/guides/home-buying-process/"
});