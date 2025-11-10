function Menu() {
    const [menu, setMenu] = React.useState([]);
    const [mobileOpen, setMobileOpen] = React.useState(false);

    React.useEffect(() => {
        fetch("menu.json")
            .then(r => r.json())
            .then(data => {
                // Add open flag for dropdowns
                setMenu(data.map(m => ({ ...m, open: false })));
            });
    }, []);

    function toggleMobileMenu() {
        setMobileOpen(!mobileOpen);
    }

    function toggleItem(clickedItem, e) {
        e.stopPropagation();

        if (!clickedItem.children) return;

        setMenu(oldMenu =>
            oldMenu.map(m =>
                m.text === clickedItem.text
                    ? { ...m, open: !m.open }
                    : { ...m, open: false }
            )
        );
    }

    return (
        <nav className="navbar navbar-expand-lg">
            <div className="mobile-toggle" onClick={toggleMobileMenu}>
                ☰ Menu
            </div>

            <div className={`menu ${mobileOpen ? "open" : ""}`}>
                {menu.map(item => (
                    <div
                        key={item.text}
                        className={`item ${item.open ? "open" : ""} ${item.children ? "clickable" : ""}`}
                        onClick={(e) => toggleItem(item, e)}
                    >
                        <a href={item.href || "#"}>{item.text}</a>

                        {item.children && (
                            <div className="dropdown">
                                {item.children.map(child => (
                                    <a key={child.text} href={child.href}>
                                        {child.text}
                                    </a>
                                ))}
                            </div>
                        )}
                    </div>
                ))}
            </div>
        </nav>
    );
}

// Mount the component
ReactDOM.createRoot(document.getElementById("menu-root")).render(<Menu />);