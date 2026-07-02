const app = Vue.createApp({});

app.component("menu-control", {
    template:
        `<nav class="navbar navbar-expand-lg">
            <div class="mobile-toggle" @click="toggleMenu">☰ Menu</div>
            <div :class="['menu', { open: isOpen }]" ref="menu">

                <div
                  class="item"
                  v-for="m in menu"
                  :key="m.text"
                  :class="{ open: m.open, clickable: m.children }"
                  @click.stop="toggleItem(m)"
                >
                      <a :href="m.href || '#'">
                        {{ m.text }}
                      </a>

                      <!-- Dropdown children -->
                      <div class="dropdown" v-if="m.children">
                        <a
                          v-for="child in m.children"
                          :href="child.href"
                          :key="child.text"
                        >
                          {{ child.text }}
                        </a>
                      </div>
                </div>
            </div>
        </nav>
        `,
    data() {
        return {
            isOpen: false,
            menu: [],
        };
    },
    mounted() {
        // Load the JSON file
        fetch("./menu.json")
            .then(r => r.json())
            .then(data => {
                this.menu = data;
            });
    },
    created() {
        this.menu.forEach(m => {
            m.open = false; // ensure reactivity
        });
    }, 
    methods: {
        toggleMenu() {
            this.isOpen = !this.isOpen;
        },

        toggleItem(m) {
            // Items with no children: behave like normal links
            if (!m.children) return;

            // Toggle only this item
            m.open = !m.open;

            // Close every other dropdown
            this.menu.forEach(i => {
                if (i !== m) i.open = false;
            });
        },
    }
});

app.component("news-rotator", {
    template: `
    <div class="row news news-rotator">
        <!-- Display the latest date -->
        <div class="col-2 latest latest-date" v-if="latestDate">
            <h2>Latest Update</h2>
            {{ latestDate }}
        </div>
        <div class="col-10">
          <!-- Display rotating headlines -->
          <div v-if="visible.length > 0">
            <div class="news-item" v-for="item in visible" :key="item.id">
              <h1><a :href="'news.htm#news_' + item.id">{{ item.header }}</a>
            </div>
          </div>
        </div>
    </div>
  `,

    data() {
        return {
            items: [],
            index: 0,      // where in the list we are
            visible: [],    // two headlines at a time
            latestDate: ""
        };
    },

    mounted() {
        // Load the JSON file
        fetch("/calendar/news.php") //news_data.json | /calendar/news.php | http://pfga_calendar/news.php for local
            .then(r => r.json())
            .then(data => {
                this.items = data;
                this.findLatestDate();
                this.updateVisible();   // show first two
                this.startRotation();   // begin rotation
            });
    },

    methods: {
        // Find the newest date in the JSON
        findLatestDate() {
            if (this.items.length === 0) return;

            // Sort by date descending
            const sorted = [...this.items].sort((a, b) => {
                return new Date(b.publish_date) - new Date(a.publish_date);
            });

            this.latestDate = sorted[0].publish_date;
        },
        updateVisible() {
            if (this.items.length > 1) {
                // rotate two at a time
                this.visible = [
                    this.items[this.index],
                    this.items[(this.index + 1) % this.items.length]
                ];
            } else {
                this.visible = [
                    this.items[this.index]
                ];
            }
        },

        startRotation() {
            if (this.items.length > 1) {
                setInterval(() => {
                    this.index = (this.index + 2) % this.items.length;
                    this.updateVisible();
                }, 5000); // rotate every 5 seconds
            }
            
        }
    }
});

app.component("news-viewer", {
    template: `
        <div class="row newsRow" v-for="item in items" :key="item.id" :id="'news_' + item.id" :class="{ 'alt': item.id % 2 === 0 }" >
            <div class="col-12">
                <div class="row">
                    <div class="col-12" >
                        <h2>{{ item.header }}</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12" v-html="item.description">
                    
                    </div>
                </div>
            </div>
        </div>`,
    data() {
        return {
            items: [],
            index: 0,      // where in the list we are
            visible: [],    // two headlines at a time
            latestDate: ""
        };
    },
    mounted() {
        // Load the JSON file
        fetch("/calendar/news.php") //news_data.json | /calendar/news.php | http://pfga_calendar/news.php for local
            .then(r => r.json())
            .then(data => {
                this.items = data;
            });
    },
});

app.mount(".app");