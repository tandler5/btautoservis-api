<div>
   <div class="containter mt-5 pt-5">
    <div class="row">
        <div class="col">
            <h2 class="mb-4">Send Real Time message</h2>
            <form class="form" wire:submit.prevent="triggerEvent">
                <input wire:model="message"  type="text" class="form-control" placeholder="Your Message">
                <input type="submit" class="btn btn-primary mt-3">
            </form>
        </div>
    </div>
   </div>
</div>
