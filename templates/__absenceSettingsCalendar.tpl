{if ABSENCE_CALENDAR_CHOOSE}
    <section class="section">
        <h2 class="sectionTitle">{lang}wcf.user.absence.setting.calendar{/lang}</h2>

        <dl>
            <dt></dt>
            <dd>
                <label><input type="checkbox" id="absentCalendar" name="absentCalendar" value="1"{if $absentCalendar} checked{/if}> {lang}wcf.user.absence.setting.calendar.create{/lang}</label>
                <small>{lang}wcf.user.absence.setting.calendar.create.description{/lang}</small>
            </dd>
        </dl>
    </section>
{/if}
