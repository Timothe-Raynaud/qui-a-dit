import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        setTimeout(() => {
            this.dismiss();
        }, 5000);

        this.element.addEventListener("mouseenter", this.handleMouseEnter.bind(this));
        this.element.addEventListener("mouseleave", this.handleMouseLeave.bind(this));
    }

    handleMouseEnter() {
        clearTimeout(this.dismissTimeout);
    }

    handleMouseLeave() {
        this.dismissTimeout = setTimeout(() => {
            this.dismiss();
        }, 1000);
    }

    dismiss() {
        if (!this.element.matches(':hover')){
            this.element.remove();
        }
    }

    close(event){
        let element = event.currentTarget;
        element.parentNode.remove()
    }
}