<?php

namespace Code16\Sharp\Tests\Feature\Api;

use Code16\Sharp\EntitiesList\EntitiesListState;
use Code16\Sharp\Exceptions\Form\SharpApplicativeException;
use Code16\Sharp\Tests\Fixtures\PersonSharpEntitiesList;
use Code16\Sharp\Tests\Fixtures\User;
use Code16\Sharp\Tests\SharpTestCase;

class EntityStateControllerTest extends SharpTestCase
{
    /** @test */
    public function we_can_update_the_state_of_an_entity()
    {
        $this->buildTheWorld();

        $this->json('post', '/sharp/api/list/person/state/1', [
                "attribute" => "state",
                "value" => "ok"
            ])
            ->assertStatus(200)
            ->assertJson([
                "action" => "refresh",
                "value" => "ok",
            ]);
    }

    /** @test */
    public function we_can_return_a_reload_action_on_state_update()
    {
        $this->buildTheWorld();

        $this->json('post', '/sharp/api/list/person/state/1', [
            "attribute" => "state",
            "value" => "ok_reload"
        ])
            ->assertStatus(200)
            ->assertJson([
                "action" => "reload",
                "value" => "ok_reload",
            ]);
    }

    /** @test */
    public function we_can_return_a_refresh_listed_items_action_on_state_update()
    {
        $this->buildTheWorld();

        $this->json('post', '/sharp/api/list/person/state/1', [
                "attribute" => "state",
                "value" => "ok_refresh_items"
            ])
            ->assertStatus(200)
            ->assertJson([
                "action" => "refresh",
                "items" => [1],
                "value" => "ok_refresh_items",
            ]);
    }

    /** @test */
    public function we_cant_update_the_state_of_an_entity_with_a_wrong_stateId()
    {
        $this->buildTheWorld();

        $this->app['config']->set(
            'sharp.entities.person.list',
            EntityStatePersonSharpEntitiesList::class
        );

        $this->json('post', '/sharp/api/list/person/state/1', [
                "attribute" => "state",
                "value" => "invalid"
            ])
            ->assertStatus(422);
    }

    /** @test */
    public function applicative_exception_is_sent_back_as_417()
    {
        $this->buildTheWorld();

        $this->app['config']->set(
            'sharp.entities.person.list',
            EntityStatePersonSharpEntitiesList::class
        );

        $this->json('post', '/sharp/api/list/person/state/1', [
                "attribute" => "state",
                "value" => "ko"
            ])
            ->assertStatus(417);
    }

    protected function buildTheWorld()
    {
        $this->actingAs(new User);

        $this->app['config']->set(
            'sharp.entities.person.list',
            EntityStatePersonSharpEntitiesList::class
        );

        $this->app['config']->set(
            'app.key', 'base64:'.base64_encode(random_bytes(
                $this->app['config']['app.cipher'] == 'AES-128-CBC' ? 16 : 32
            ))
        );
    }
}

class EntityStatePersonSharpEntitiesList extends PersonSharpEntitiesList {

    function buildListConfig()
    {
        $this->setEntityStateHandler("state", new class() extends EntitiesListState {

            protected function buildStates()
            {
                $this->addState("ok", "OK", "blue");
                $this->addState("ok_reload", "OK", "blue");
                $this->addState("ok_refresh_items", "OK", "blue");
                $this->addState("ko", "KO2", "red");
            }

            protected function updateState($instanceId, $stateId)
            {
                if($stateId == "ko") {
                    throw new SharpApplicativeException("Nope");
                }

                if($stateId == "ok_reload") {
                    return $this->reload();
                }

                if($stateId == "ok_refresh_items") {
                    return $this->refresh([$instanceId]);
                }
            }
        });
    }
}