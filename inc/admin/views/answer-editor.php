<div class="lp-box-data-content">
    <div class="learn-press-question">
        <div class="description">
            <table class="lp-list-options list-question-answers">
                <thead>
                    <tr>
                        <th class="sort"></th>
                        <th class="order">#</th>
                        <th class="answer-text"><?php _e( 'Label', 'htc-softable' ); ?></th>
                        <th class="answer-correct"><?php _e( 'Sort', 'htc-softable' ); ?></th>
                        <th class="actions"></th>
                    </tr>
                </thead>
                <tbody>
                    <lp-question-sortable-answer-option v-for="(answer, index) in answers" :key="index" :index="index" 
                                            :number="number" :answer="answer"
                                           @updateTitle="updateTitle"
                                           @changeCorrect="changeCorrect"
                                           @deleteAnswer="deleteAnswer"></lp-question-sortable-answer-option>
                </tbody>
            </table>
            <p class="add-answer">
                <button class="button add-question-option-button" type="button" @click="newAnswer"><?php esc_html_e( 'Add option', 'htc-softable' ); ?></button>
            </p>
        </div>
    </div>
</div>
